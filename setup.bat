@echo off
echo ===========================================
echo      German Quiz Master - Setup Script
echo ===========================================

cd backend

echo [INFO] Removing existing node_modules...
rmdir /s /q node_modules
del /f /q package-lock.json

echo [INFO] Installing dependencies...
call npm install
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Failed to install dependencies.
    pause
    exit /b %ERRORLEVEL%
)

echo [SUCCESS] Setup complete. You can now use run_project.bat
pause
