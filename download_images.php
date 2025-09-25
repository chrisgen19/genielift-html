<?php
/**
 * Genie Website Image Downloader
 * Downloads all images from the HTML and saves them to /images/ directory
 */

// Set execution time limit for large downloads
set_time_limit(300); // 5 minutes

// Create images directory if it doesn't exist
$imageDir = __DIR__ . '/images/';
if (!is_dir($imageDir)) {
    mkdir($imageDir, 0755, true);
    echo "Created images directory: $imageDir\n";
}

// Array of all images found in the HTML with their desired local names
$images = [
    // Header logos
    'https://web.archive.org/web/20190307013226im_/https://www.genielifts.com.au/wp-content/uploads/2018/07/logo.png' => 'logo.png',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/08/ag-logo-blue.png' => 'ag-logo-blue.png',
    
    // Hero slider images
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/07/home-slide1-1920x400_c.jpg' => 'home-slide1-1920x400_c.jpg',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/07/home-slide2-1920x400_c.jpg' => 'home-slide2-1920x400_c.jpg',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/08/slideshow-service-van-1920x400_c.jpg' => 'slideshow-service-van-1920x400_c.jpg',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/07/home-slide5-1920x400_c.jpg' => 'home-slide5-1920x400_c.jpg',
    
    // Silhouette category images
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/08/silhouette-material-lift.png' => 'silhouette-material-lift.png',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/08/silhouette-platform.png' => 'silhouette-platform.png',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/08/silhouette-scissor.png' => 'silhouette-scissor.png',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/08/silhouette-articulting.png' => 'silhouette-articulting.png',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/08/silhouette-telescopic.png' => 'silhouette-telescopic.png',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/08/silhouette-trailer.png' => 'silhouette-trailer.png',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/08/silhouette-telehandler.png' => 'silhouette-telehandler.png',
    
    // 5 Ways section images
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/08/quin-1.jpg' => 'quin-1.jpg',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/08/quin-2.jpg' => 'quin-2.jpg',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/08/quin-3.jpg' => 'quin-3.jpg',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/08/quin-4.jpg' => 'quin-4.jpg',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/08/quin-5.jpg' => 'quin-5.jpg',
    
    // Footer images
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/07/bottom-logo-taking.png' => 'bottom-logo-taking.png',
    'https://web.archive.org/web/20190307013028im_/https://www.genielifts.com.au/wp-content/uploads/2018/07/partnering-21-years.png' => 'partnering-21-years.png'
];

/**
 * Download image using cURL
 */
function downloadImage($url, $localPath) {
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => "cURL Error: $error"];
    }
    
    if ($httpCode !== 200) {
        return ['success' => false, 'error' => "HTTP Error: $httpCode"];
    }
    
    if (empty($imageData)) {
        return ['success' => false, 'error' => "No data received"];
    }
    
    // Save the image
    $result = file_put_contents($localPath, $imageData);
    
    if ($result === false) {
        return ['success' => false, 'error' => "Failed to write file"];
    }
    
    return ['success' => true, 'size' => $result];
}

/**
 * Format file size for display
 */
function formatBytes($size, $precision = 2) {
    if ($size > 0) {
        $size = (int) $size;
        $base = log($size) / log(1024);
        $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }
    return $size;
}

// Start downloading
echo "🚀 Starting image download process...\n";
echo "📁 Images will be saved to: $imageDir\n";
echo "📊 Total images to download: " . count($images) . "\n";
echo str_repeat("-", 80) . "\n";

$successCount = 0;
$failCount = 0;
$totalSize = 0;
$startTime = microtime(true);

foreach ($images as $url => $filename) {
    $localPath = $imageDir . $filename;
    
    // Skip if file already exists
    if (file_exists($localPath)) {
        echo "⏭️  SKIP: $filename (already exists)\n";
        continue;
    }
    
    echo "📥 Downloading: $filename ... ";
    
    $result = downloadImage($url, $localPath);
    
    if ($result['success']) {
        $successCount++;
        $totalSize += $result['size'];
        echo "✅ SUCCESS (" . formatBytes($result['size']) . ")\n";
    } else {
        $failCount++;
        echo "❌ FAILED: " . $result['error'] . "\n";
    }
    
    // Small delay to be respectful to the server
    usleep(500000); // 0.5 seconds
}

$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

echo str_repeat("-", 80) . "\n";
echo "📈 DOWNLOAD SUMMARY:\n";
echo "✅ Successful: $successCount\n";
echo "❌ Failed: $failCount\n";
echo "📦 Total size: " . formatBytes($totalSize) . "\n";
echo "⏱️  Time taken: {$duration} seconds\n";

if ($successCount > 0) {
    echo "\n🎉 Download completed! Your images are now in the /images/ directory.\n";
    echo "💡 Next step: Update your HTML to use local image paths like 'images/logo.png'\n";
} else {
    echo "\n⚠️  No images were downloaded successfully. Please check the error messages above.\n";
}

// Generate updated HTML image paths for reference
echo "\n📝 Updated image paths for your HTML:\n";
echo str_repeat("-", 50) . "\n";
foreach ($images as $url => $filename) {
    echo "OLD: $url\n";
    echo "NEW: images/$filename\n\n";
}

?>