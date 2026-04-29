Set-Location 'C:\laragon\www\vibe-coding'

$env:PLAYWRIGHT_BASE_URL = 'http://127.0.0.1:8000'
$node = 'C:\laragon\bin\nodejs\node-v22\node.exe'
$playwrightCli = '.\node_modules\playwright\cli.js'

& $node $playwrightCli install chromium
& $node $playwrightCli test --project=chromium --headed --reporter=html
& $node $playwrightCli show-report
