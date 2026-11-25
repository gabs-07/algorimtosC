<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Enlace Dos</title>
</head>
<body>

    <nav>
        <a href="{{route('rutaHome')}}">Home</a>
        <a href="{{route('rutaEnlaceUno')}}">Ruta Uno</a>
        <a href="{{route('rutaEnlaceDos')}}">Ruta Dos</a>
        <a href="{{route('rutaEnlaceTres')}}">Ruta Tres</a>
    </nav>

    <h1>Pagina Dos</h1>

    <style>
        *{
            margin: 0;
            padding: 0;
        }
        nav{
            display: flex;
            flex-direction: row;
            gap: 1rem;
            justify-content: center;
            align-items: center;
            width: auto; 
            height: 100px;
            background-color: rgb(10, 8, 110);
        }

        a{
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-family: Arial, Helvetica, sans-serif;
        }

    </style>

</body>
</html>