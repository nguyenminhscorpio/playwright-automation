@echo off
setlocal
cd /d "%~dp0"

set "NODE_EXE=C:\laragon\bin\nodejs\node-v22\node.exe"

if not exist "%NODE_EXE%" (
  echo Node was not found at "%NODE_EXE%".
  exit /b 1
)

"%NODE_EXE%" ".\node_modules\playwright\cli.js" install chromium
exit /b %ERRORLEVEL%
