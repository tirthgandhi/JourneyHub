<?php
/**
 * Image Helper Functions — JourneyHub
 * Provides fallback for missing images
 */

function getTripImage($imagePath) {
    if (empty($imagePath)) {
        return '/JourneyHub/assets/images/placeholder-trip.jpg';
    }
    
    $fullPath = __DIR__ . '/../assets/images/covers/' . $imagePath;
    if (file_exists($fullPath)) {
        return '/JourneyHub/assets/images/covers/' . htmlspecialchars($imagePath);
    }
    
    return '/JourneyHub/assets/images/placeholder-trip.jpg';
}

function getCityImage($cityName) {
    if (empty($cityName)) {
        return '/JourneyHub/assets/images/placeholder-city.jpg';
    }
    
    $filename = strtolower(str_replace(' ', '-', $cityName)) . '.jpg';
    $fullPath = __DIR__ . '/../assets/images/cities/' . $filename;
    
    if (file_exists($fullPath)) {
        return '/JourneyHub/assets/images/cities/' . $filename;
    }
    
    return '/JourneyHub/assets/images/placeholder-city.jpg';
}

function getActivityImage($activityName) {
    if (empty($activityName)) {
        return '/JourneyHub/assets/images/placeholder-activity.jpg';
    }
    
    $filename = strtolower(str_replace(' ', '-', $activityName)) . '.jpg';
    $fullPath = __DIR__ . '/../assets/images/activities/' . $filename;
    
    if (file_exists($fullPath)) {
        return '/JourneyHub/assets/images/activities/' . $filename;
    }
    
    return '/JourneyHub/assets/images/placeholder-activity.jpg';
}
