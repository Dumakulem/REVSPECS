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
        /* Animation keyFrames */
        @keyframes slideLeft {
            from {
                transform: translateX(-100%);
                opacity: 0
            }
            to {
                transform: translateX(0);
                opacity: 1
            }
        }

        
        body {
            background: var(--bg);
            display: flex;
            flex-direction: column;
            color:var(--text);
        }
        
        .nav-top{
            display: flex;
            /* height: auto; */
            background: var(--accent);
            align-items: center;
            /* width: auto; */
            border-bottom: 1px solid black;
            /* justify-content: right; */
        }

        .header-left {
            display: flex;
            border: 1px solid black;
            padding-left: 0.67rem;
            /* width: auto; */
            align-items: center;
            /* justify-content: center; */
            flex-shrink: 0;
        }

        .header-right{
            display:flex;
            flex-shrink: 0;
            align-items: center;
            border: 1px solid black;
            /* width: 25%; */
            justify-content: flex-end;
            padding-right: 0.67rem;
            /* margin:0.67rem; */
            /* text-align: center; */
        }
            
        .header-center{
            flex: 1;
        }
        .header-right .logo{
            height: 5rem;
            /* margin: 0.67rem; */
        }

        .base {
            background: var(--surface);
            margin: 1.25rem;
            /* height: 50rem; */
            padding: 2rem;
            max-width: 40rem;
            animation: slideLeft 2s ease-out forwards;
            border-radius: 0.67rem;
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
        <h1>Welcome to REVSPECS!</h1>
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Aut eius alias, dolore error, consequuntur quos ipsam, tenetur qui ab enim sit reiciendis laboriosam. Ullam rem, illo eos exercitationem numquam perspiciatis!</p>
    </div>
</div>

</body>
</html>