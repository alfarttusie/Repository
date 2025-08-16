<?php


class GitHubAutoUpdater
{
    private string $githubUser = "alfarttusie";
    private string $repository = "Repository";
    private string $branchName = "main";

    private array $ignoredPaths = ['.git', '.github', 'README.md', 'doc', 'update.php', 'db.php', 'fonts'];

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

        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . "php";
        $remoteFileList = $this->getRemoteFileList();
        $localFileList = $this->getLocalFileList();

        foreach ($remoteFileList as $remoteFile) {
            if ($this->isIgnored($remoteFile)) continue;


            $url = $this->fixUrlPath("https://raw.githubusercontent.com/{$this->githubUser}/{$this->repository}/{$this->branchName}/$remoteFile");

            $localFile = $path . DIRECTORY_SEPARATOR . $remoteFile;

            switch (true) {
                case !file_exists($localFile):
                    print("Downloading new file: $remoteFile\n");
                    $this->downloadRemoteFile($remoteFile);
                    $this->syncLog['new'][] = $remoteFile;
                    break;
                case hash_file('sha1', $localFile) !== sha1(@file_get_contents($url)):
                    unlink($localFile);
                    $this->downloadRemoteFile($remoteFile);
                    $this->syncLog['updated'][] = $remoteFile;
                    print("Updating file: $remoteFile\n");
                    break;
            }
        }
        $deletedFiles = array_diff($localFileList, $remoteFileList);
        foreach ($deletedFiles as $localOnlyPath) {
            if (is_file($localOnlyPath)) {
                unlink($path . DIRECTORY_SEPARATOR . $localOnlyPath);
                $this->syncLog['deleted'][] = $localOnlyPath;
            }
        }
        if ($this->syncLog['new'] || $this->syncLog['updated'] || $this->syncLog['deleted'])
            return new Response(200, ['status' => 'updated']);
        else
            return new Response(200, ['status' => 'no changes']);
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
        $url = $this->fixUrlPath($url);
        $context = stream_context_create($this->httpOptions);
        $remoteContent = @file_get_contents($url, false, $context);

        $dir = dirname($relativePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($relativePath, $remoteContent);
    }
    private function getLocalFileList($dir = null): array
    {
        $fileList = [];

        $startDir = $dir !== null ? $dir : dirname(__DIR__);
        $root = realpath($startDir);

        if ($root === false || !is_dir($root)) return $fileList;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) continue;

            $relativePath = str_replace($root . DIRECTORY_SEPARATOR, '', $fileInfo->getPathname());

            if ($this->isIgnored($relativePath))  continue;

            $fileList[] = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        }

        return $fileList;
    }
    private function fixUrlPath(string $url): string
    {
        $url = str_replace('\\', '/', $url);
        $url = preg_replace('#(?<!:)//+#', '/', $url);
        return $url;
    }
}