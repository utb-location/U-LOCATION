Set shell = CreateObject("WScript.Shell")
command = """C:\wamp64\bin\php\php8.3.6\php.exe"" ""C:\wamp64\www\U-LOCATION-LARAVEL\artisan"" schedule:run"
shell.Run command, 0, True
