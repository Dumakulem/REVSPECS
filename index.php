<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REVSPECS</title>
    <style>
        /* color scheme */
        :root {
            --bg: #325334;
            --surface: #e75f2079;
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
            /* justify-content: right; */
            
            

        }

        .header-left {
            border: 1px solid black;
            width: 25%;
            align-items: center;
            justify-content: center;
        }
        /* .header-one {
            display: flex;
            align-items:  right;
            border: 1px solid black;
            justify-content: right;
        } */

            .header-right{
            border: 1px solid black;
            width: 25%;
            text-align: center;
            }
            
            .header-center{
                width: 50%;
            }
        .header-left .logo{
            height: 6.7rem;
            margin: 0.67rem;
        }


        /* .body-main{
            display: flex;
            flex-direction: column;
        } */
        .base {
            background: var(--surface);
            opacity: 1;
            margin: 1.25rem;
            height: 50rem;
            

        }



    </style>
</head>
<body>
    

<div class="body-main">
    <div class="nav-top">

    <div class = "header-left">
    <img  class="logo" src="Specs Logo.png">

    </div>
     <div class="header-center">

     </div>

    <div class="header-right">
        <h1>REVSPECS</h1>
    </div>


    </div>
    <div class="base">
        gyat gyat
    </div>
</div>

</body>
</html>