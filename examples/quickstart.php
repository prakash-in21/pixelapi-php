<?php
require __DIR__ . "/vendor/autoload.php";

use PixelAPI\PixelAPI;

$client = new PixelAPI(getenv("PIXELAPI_KEY"));

// Generate AI image
$r = $client->generate("product photo of red sneakers, white background, studio lighting");
echo "generated: " . $r["output_url"] . " (used " . $r["credits_used"] . " credits)\n";
$client->save($r, "out/sneakers.png");

// Remove background
$r = $client->removeBackground("https://pixelapi.dev/demo/photo.jpg");
echo "bg-removed: " . $r["output_url"] . " (used " . $r["credits_used"] . " credits)\n";
