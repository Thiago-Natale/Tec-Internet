<?php
session_start()
?>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header class="top-bar">
        <div>
            <h1>Olá <?php echo $_SESSION["nome"]; ?> </h1>
        </div>
        <button value="sair" id="sair">sair</button>
    </header>
    <main>
        <section>
            <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Magnam, molestiae. Tempore maiores quos, amet, similique incidunt nostrum harum ad laudantium saepe veniam consequuntur excepturi. Id perferendis et corporis quo illum!</p>
        </section>
    </main>
</body>

</html>