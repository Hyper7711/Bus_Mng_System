<?php
// ai/smart_search.php

class SmartRouteSearch {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Fuzzy search - handles typos and partial matches
     */
    public function fuzzySearch($user_input) {
        $user_input = trim(strtolower($user_input));
        
        $locations = $this->getAllLocations();
        
        $matches = [];
        foreach ($locations as $location) {
            $similarity = 0;
            
            if (strtolower($location) === $user_input) {
                $similarity = 100;
            }
            elseif (strpos(strtolower($location), $user_input) !== false) {
                $similarity = 80;
            }
            else {
                $lev = levenshtein(strtolower($location), $user_input);
                $max_len = max(strlen($location), strlen($user_input));
                if ($max_len > 0) {
                    $similarity = (1 - ($lev / $max_len)) * 100;
                }
            }
            
            if (soundex($location) === soundex($user_input)) {
                $similarity = max($similarity, 70);
            }
            
            if ($similarity > 40) {
                $matches[] = [
                    'location' => $location,
                    'similarity' => round($similarity, 1)
                ];
            }
        }
        
        usort($matches, function($a, $b) {
            return $b['similarity'] - $a['similarity'];
        });
        
        return array_slice($matches, 0, 5);
    }
    
    /**
     * Recommend best route (IMPROVED LOGIC)
     */
    public function recommendRoute($source, $destination, $travel_date) {
        
        // 🔥 Step 1: Auto-correct using fuzzy search
        $srcSuggestions = $this->fuzzySearch($source);
        $destSuggestions = $this->fuzzySearch($destination);
        
        $source = !empty($srcSuggestions) ? $srcSuggestions[0]['location'] : $source;
        $destination = !empty($destSuggestions) ? $destSuggestions[0]['location'] : $destination;

        // 🔥 Step 2: Flexible query (NOT strict)
        $query = "SELECT r.*, b.bus_number as bus_name, b.capacity
                  FROM routes r
                  JOIN buses b ON r.route_id = b.route_id
                  WHERE LOWER(r.start_point) LIKE LOWER(?) 
                     OR LOWER(r.end_point) LIKE LOWER(?)";
        
        $src = "%{$source}%";
        $dest = "%{$destination}%";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$src, $dest]);
        
        $routes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            
            $available_seats = $row['capacity'];
            $occupancy_rate = 0;
            
            $score = $this->calculateRouteScore($row, $available_seats, $occupancy_rate);
            
            $row['available_seats'] = $available_seats;
            $row['occupancy_rate'] = $occupancy_rate;
            $row['ai_score'] = $score;
            $row['recommendation'] = $this->getRecommendationTag($score, $available_seats);
            
            $routes[] = $row;
        }
        
        usort($routes, function($a, $b) {
            return $b['ai_score'] - $a['ai_score'];
        });
        
        return $routes;
    }
    
    private function calculateRouteScore($route, $available_seats, $occupancy_rate) {
        $score = 50;
        
        if ($available_seats > 10) $score += 20;
        elseif ($available_seats > 5) $score += 15;
        elseif ($available_seats > 0) $score += 5;
        else $score -= 30;
        
        if (isset($route['fare'])) {
            if ($route['fare'] < 200) $score += 15;
            elseif ($route['fare'] < 500) $score += 10;
            elseif ($route['fare'] < 1000) $score += 5;
        }
        
        if (isset($route['bus_type'])) {
            if (strtolower($route['bus_type']) === 'ac') $score += 10;
            if (strtolower($route['bus_type']) === 'sleeper') $score += 8;
        }
        
        return min(100, max(0, $score));
    }
    
    private function getRecommendationTag($score, $available_seats) {
        if ($available_seats <= 0) return '❌ Sold Out';
        if ($score >= 80) return '⭐ Best Choice';
        if ($score >= 60) return '👍 Recommended';
        if ($score >= 40) return '✅ Available';
        return '⚡ Limited';
    }
    
    /**
     * Get all locations
     */
    private function getAllLocations() {
        $query = "SELECT DISTINCT start_point as location FROM routes
                  UNION
                  SELECT DISTINCT end_point as location FROM routes";
        
        $stmt = $this->conn->query($query);
        
        $locations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $locations[] = $row['location'];
        }
        
        return $locations;
    }
}
?>