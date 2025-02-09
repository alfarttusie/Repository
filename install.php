<?php if (file_exists('php/db.php')) exit(header('Location: index.php')); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Install - Repository</title>
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/animation.css" />
    <link rel="stylesheet" href="css/install.css" />
</head>

<body>
    <form class="install-holder">
        <div class="input-group">
            <label for="db_username">MySQL User:</label>
            <input type="text" id="db_username" class="db_username" placeholder="Enter MySQL Username" required />
        </div>
        <div class="input-group">
            <label for="db_password">MySQL Password:</label>
            <input type="password" id="db_password" class="db_password"
                placeholder="Enter Password (leve it empty if none)" />
        </div>
        <div class="input-group">
            <label for="db_name">Database Name:</label>
            <input type="text" id="db_name" class="db_name" placeholder="Enter Database Name" required />
        </div>
        <div class="input-group">
            <label for="username">Administration :</label>
            <input type="text" id="username" class="username" placeholder="Enter login Username" required>
        </div>
        <div class="input-group">
            <label for="Password">Administration Password:</label>
            <input type="text" id="Password" class="Password" placeholder="Enter Administration Password" required>
        </div>
        <button type="submit" class="install-button" value="install">Install</button>
        <button type="submit" class="install-button" value="backup">backup</button>
    </form>
    <script src="js/install.js"></script>
    <script src='js/assistant.js'></script>
</body>

</html>