param(
    [string]$StatusFile,
    [int]$IdleLimit
)

if (!(Test-Path $StatusFile)) {
    exit 0
}

$last = (Get-Item $StatusFile).LastWriteTime
$idle = (New-TimeSpan -Start $last -End (Get-Date)).TotalSeconds

if ($idle -gt $IdleLimit) {
    exit 1
}

exit 0
