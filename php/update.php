<?php


class GitHubAutoUpdater
{
    private string $githubUser = "alsabah313";
    private string $repository = "testupdate";
    private string $branchName = "main";

    private array $ignoredPaths = ['.git', '.github', 'README.md', 'update.php'];

    private array $httpOptions = [
        'http' => [
            'header' => "User-Agent: PHP\r\n"
        ]
    ];

    private array $syncLog = [
        'new' => [],
        'updated' => [],
        'deleted' => []
    ];

    public function __construct()
    {
        $remoteFileList = $this->getRemoteFileList();
        $localFileList = $this->getLocalFileList();

        foreach ($remoteFileList as $relativePath) {
            if ($this->isIgnored($relativePath)) continue;

            switch (true) {
                case !file_exists($relativePath):
                    $this->downloadRemoteFile($relativePath);
                    $this->syncLog['new'][] = $relativePath;
                    break;

                case @file_get_contents($relativePath) != @file_get_contents("https://raw.githubusercontent.com/{$this->githubUser}/{$this->repository}/{$this->branchName}/$relativePath"):
                    $this->downloadRemoteFile($relativePath);
                    $this->syncLog['updated'][] = $relativePath;
                    break;
            }
        }

        $deletedFiles = array_diff($localFileList, $remoteFileList);
        foreach ($deletedFiles as $localOnlyPath) {
            if (is_file($localOnlyPath)) {
                unlink($localOnlyPath);
                $this->syncLog['deleted'][] = $localOnlyPath;
            }
        }
    }

    private function isIgnored(string $path): bool
    {
        foreach ($this->ignoredPaths as $ignored) {
            if (stripos($path, $ignored) !== false) return true;
        }
        return false;
    }

    private function getRemoteFileList(): array
    {
        $fileList = [];
        $apiUrl = "https://api.github.com/repos/{$this->githubUser}/{$this->repository}/git/trees/{$this->branchName}?recursive=1";
        $context = stream_context_create($this->httpOptions);
        $response = file_get_contents($apiUrl, false, $context);
        $tree = json_decode($response, true)['tree'] ?? [];

        foreach ($tree as $item) {
            if ($item['type'] !== 'blob') continue;
            if (!$this->isIgnored($item['path'])) {
                $fileList[] = str_replace("/", DIRECTORY_SEPARATOR, $item['path']);
            }
        }

        return $fileList;
    }

    private function downloadRemoteFile(string $relativePath)
    {
        $url = "https://raw.githubusercontent.com/{$this->githubUser}/{$this->repository}/{$this->branchName}/{$relativePath}";
        $context = stream_context_create($this->httpOptions);
        $remoteContent = @file_get_contents($url, false, $context);

        $dir = dirname($relativePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($relativePath, $remoteContent);
    }

    private function getLocalFileList(): array
    {
        $fileList = [];
        $root = __DIR__;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            $relativePath = str_replace($root . DIRECTORY_SEPARATOR, '', $fileInfo->getPathname());
            if ($this->isIgnored($relativePath)) continue;
            $fileList[] = $relativePath;
        }

        return $fileList;
    }
}

new GitHubAutoUpdater();