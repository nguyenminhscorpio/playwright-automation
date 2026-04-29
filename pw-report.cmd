@echo off
setlocal
cd /d "%~dp0"

set "PLAYWRIGHT_BASE_URL=http://127.0.0.1:8000"
set "NODE_EXE=C:\laragon\bin\nodejs\node-v22\node.exe"

if not exist "%NODE_EXE%" (
  echo Node was not found at "%NODE_EXE%".
  exit /b 1
)

"%NODE_EXE%" ".\node_modules\playwright\cli.js" install chromium
if errorlevel 1 exit /b %ERRORLEVEL%

"%NODE_EXE%" ".\node_modules\playwright\cli.js" test --project=chromium --headed --reporter=html
if errorlevel 1 exit /b %ERRORLEVEL%

"%NODE_EXE%" ".\node_modules\playwright\cli.js" show-report
exit /b %ERRORLEVEL%
