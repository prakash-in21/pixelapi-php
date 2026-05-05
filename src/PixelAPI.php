<?php
namespace PixelAPI;

class PixelAPI {
    private string $apiKey;
    private string $baseUrl;

    public function __construct(string $apiKey, string $baseUrl = "https://api.pixelapi.dev") {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, "/");
    }

    private function post(string $endpoint, array $data): array {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_HTTPHEADER => ["X-API-Key: " . $this->apiKey],
            CURLOPT_POSTFIELDS => $data,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 401) throw new PixelAPIException("invalid API key", 401);
        if ($code === 402) throw new PixelAPIException("insufficient credits", 402);
        if ($code === 429) throw new PixelAPIException("rate limit exceeded", 429);
        if ($code >= 400) throw new PixelAPIException("PixelAPI error $code: $body", $code);
        $r = json_decode($body, true);
        if (!is_array($r)) throw new PixelAPIException("invalid JSON response", 500);
        return $r;
    }

    public function generate(string $prompt): array {
        return $this->post("/v1/image/generate", ["prompt" => $prompt]);
    }

    public function removeBackground(string $imageUrl): array {
        return $this->post("/v1/image/remove-background", ["image_url" => $imageUrl]);
    }

    public function upscale(string $imageUrl, int $scale = 4): array {
        return $this->post("/v1/image/upscale", ["image_url" => $imageUrl, "scale" => $scale]);
    }

    public function faceRestore(string $imageUrl): array {
        return $this->post("/v1/image/face-restore", ["image_url" => $imageUrl]);
    }

    public function save(array $result, string $path): void {
        if (empty($result["output_url"])) throw new PixelAPIException("no output_url in result", 500);
        $bytes = file_get_contents($result["output_url"]);
        if ($bytes === false) throw new PixelAPIException("failed to download output", 500);
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
        file_put_contents($path, $bytes);
    }
}

class PixelAPIException extends \Exception {}
