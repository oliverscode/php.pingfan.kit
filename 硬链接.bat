@echo off
set /p targetDir="Please enter the target directory: "

if not exist "%targetDir%" (
    echo The target directory does not exist.
    goto end
)

set sourceFile=pingfan.kit.php
set destinationFile=%targetDir%\pingfan.kit.php

if not exist "%sourceFile%" (
    echo The source file pingfan.kit.php does not exist in the current directory.
    goto end
)

fsutil hardlink create "%destinationFile%" "%sourceFile%"
if %errorlevel% == 0 (
    echo File has been successfully copied to %destinationFile%.
) else (
    echo Failed to copy the file.
)

:end
rem pause
