@echo off
echo ===========================================
echo      German Quiz Master - Run Script
echo ===========================================

cd backend

if not exist node_modules (
    echo [INFO] node_modules not found. Installing dependencies...
    call npm install
    if %ERRORLEVEL% NEQ 0 (
        echo [ERROR] Failed to install dependencies.
        pause
        exit /b %ERRORLEVEL%
    )
    echo [SUCCESS] Dependencies installed.
) else (
    echo [INFO] Dependencies already installed.
)

echo [INFO] Starting server...
echo [INFO] Server will be available at http://localhost:3000
echo.

call npm start
pause
