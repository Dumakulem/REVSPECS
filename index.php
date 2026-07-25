<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REVSPECS</title>
    <style>
        /* color scheme */
        :root {
            --bg: #324e53;
            --surface: #fffaf879;
            --border: #fff;
            --text: #ffffff;
            --accent: #095000;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--bg);
            display: flex;
            flex-direction: column;
            color:var(--text);
        }
        
        .nav-top{
            display: flex;
            height: auto;
            background: var(--accent);
            align-items: center;
            width: auto;
            /* justify-content: right; */
        }

        .header-left {
            border: 1px solid black;
            padding-left: 0.67rem;
            width: auto;
            align-items: center;
            justify-content: center;
        }

        .header-right{
            display:flex;
            border: 1px solid black;
            width: 25%;
            justify-content: right;
            padding-right: 0.67rem;
            /* margin:0.67rem; */
            /* text-align: center; */
        }
            
        .header-center{
            width: 75%;
        }
        .header-right .logo{
            height: 5rem;
            /* margin: 0.67rem; */
        }

        .base {
            background: var(--surface);
            margin: 1.25rem;
            height: 50rem;
        }

    </style>
</head>
<body>

<div class="body-main">
    <div class="nav-top">

    <div class="header-left">
        <h1>REVSPECS</h1>
    </div>
     <div class="header-center">

     </div>

    <div class="header-right">
    <img class="logo" src="Specs Logo.png" alt="SPECS logo">
    </div>

    </div>
    <div class="base">
        gyat gyat
    </div>
</div>

</body>
</html>