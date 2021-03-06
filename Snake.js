//definiera canvas och funktioner för att rita på den, och ta emot events
var mycanvas = document.getElementById('mycanvas');
var ctx = mycanvas.getContext('2d');
var squareSize = 10; 
var w = 500;//bredd
var h = 500;//höjd på spelplan
var score = 0;
var delay = 80; // väntan mellan varje ormrörelse
var snake;
var squareSize = 10;
var food;
var overAndOut = false;

//design pattern men hjälp av modul som innehåller funktioner, som inte kolliderar med andra moduler. Anonyma funktioner. Ungefär som en klass i java.
var drawModule = (function (){
    var bodySnake = function(x, y) {
        //rita kanten på ormobjektet x och y.
        ctx.strokeStyle = 'darkgreen';
        ctx.strokeRect(x*squareSize, y*squareSize, squareSize, squareSize);
    }

    var lemon = function(x, y){
        //rita kanten på lemonbjektet x och y.
        ctx.fillStyle = 'yellow';
        ctx.fillRect(x*squareSize, y*squareSize, squareSize, squareSize);
    }

    var scoreText = function(){
        //hur många matbitar ormen äter, syns längst ner på canvas under spelgång.
        var scoreText = "Score: " + score + " Speed: " + (100 - delay);
        ctx.fillStyle = 'blue';
        ctx.fillText(scoreText, 145, h-5);
    }

    var drawSnake = function() {
        /*en Snake består av en array av objekt där varje 
        objekt har en x och en y variabel
        som bestämmer objektets position på spelplanen.
        Antalet objekt i arrayen bestämmer längden på ormen. Startlängden är 3.*/
        var xpos = 2;
        snake = [];

        //skapa loop av array med elementen
        for (var i = xpos; i >= 0; i--)
            //skapa ett nytt objekt med x och y position.
            snake.push({x:i, y:0});
        //nu finns det en horizontell orm som är tre objekt lång
    }

    var createFood = function() {
        food = {
            //random nummer så maten kommer på obestämd plats
            x: Math.floor((Math.random() *30)+1),
            y: Math.floor((Math.random() *30)+1)
        }

        //se så inte maten hamnar på ormen
        while(true) {
            /*kolla alla ormens objekts positioner, se om någon kolliderar med lemon,
            om det är sant, sätt collision = true*/
            var collision = false
            for(var i = 0; i> snake.length; i++) {
                var snakeObj = snake[i]

                if (food.x === snakeObj.x && food.y === snakeObj.y) {
                    collision = true;
                    break;
                }
            }
            if(!collision)
                /*ingen kollision, maten är okej*/
                break;
                //vid kollision, skapa ny mat och försök igen.
            food.x = Math.floor((Math.random() * 30) + 1)
            food.y = Math.floor((Math.random() * 30) + 1)
        }
    }

    //krash med sin egna kropp
    var checkCollision = function(x, y, array){
        for(var i = 0; i < array.length; i++){
            if(array[i].x === x && array[i].y == y)
                return true;
        }
        return false;
    }

    //spelplan, denna funktion anropas vid varje steg som ormen tar.
    var paint = function() {
        //bakgrundsfärg på spelplan
        ctx.fillStyle = 'lightgrey';
        ctx.fillRect(0, 0, w, h);

        var snakeX = snake[0].x;
        var snakeY = snake[0].y;
        //fortsätt flytta åt senast angivna riktning, "nytt huvud"
        if(direction == 'right'){
            snakeX++;
        } else if (direction == 'left') {
            snakeX--;
        } else if (direction == 'up') {
            snakeY--;
        } else if (direction == 'down') {
            snakeY++;
        }

        //kolla om ormens "nya huvud" kolliderar
        if (snakeX == -1 || snakeX == w / squareSize || snakeY == -1 || snakeY == h / squareSize || checkCollision(snakeX, snakeY, snake)) {
            //Game over, terminera timern som anropar paint funktionen
        clearInterval(gameloop)
        var wasRecord = false
        //anropar servern med resultatet
        $.ajax({
            type: "POST",
            url: 'score_handler.php',
            dataType: 'json',
            data: {functionname: 'add_score', arguments: [score]},
        
            success: function (obj, textstatus) {
                if( !('error' in obj) ) {
                    //Hämta svaret från servern
                    console.log(obj.result);
                    wasRecord = obj.result;
                    //lägg ut rätt alert beroende på om det var nytt personligt record eller inte
                    if(wasRecord) {
                        alert ("Congratulations! Your new personal record is: " + score);
                    } else {
                        alert ("Your score: " + score);
                    }
                    //Se till att sidan laddas om med dem nya resultaten, även det som möjliggör att man kan spela igen
                    window.location.reload(true);                    
                }
                else {
                    console.log(obj.error);
                }
            },
            error: function (jqXHR, status, err) {
                alert("An error occured. Status: " + status + "Error: " + err);
            },                    
        });
        return
        } 
        //för att ormen ska bli längre av att äta
        if(snakeX == food.x && snakeY == food.y){
            //ormen åt, skapa nytt huvud, bli längre
            var tail = {
                x: snakeX,
                y: snakeY
            };
            score += 2;
            if((score % 8) == 0){
                // Snabba upp spelet genom att minska väntan mellan varje rörelse med 5%
                delay -= (delay / 20).toFixed();
                clearInterval(gameloop);
                gameloop = setInterval(paint,delay);
            }
            createFood();
        } else {
            //ormen åt ingenting, svans blir huvud
            var tail = snake.pop();
            tail.x = snakeX;
            tail.y = snakeY;
        }
        //svans som första bit
        snake.unshift(tail);
        //skapa en ruta för varje element av arrayen med hjälp av bodySnake
        for(var i = 0; i < snake.length; i++){
            bodySnake(snake[i].x, snake[i].y);
        }

        lemon(food.x, food.y);
        scoreText();
    }

    var init = function() {
        //här startar programmet, ormen börjar nedåtvänd.
        direction = 'down';
        drawSnake();
        createFood();
        
        //anropa metoden paint varje 80 ms.
        gameloop = setInterval(paint,delay);
    }

    return { init: init, paint: paint };
}());

(function(window, document, drawModule, undefined){
    //koppla knappen till init
    var btn = document.getElementById('btn');
    btn.addEventListener("click", function (){
        //så att man inte kan trycka start medan man spelar
        btn.setAttribute('disabled', true);

        drawModule.init();
    });

    document.onkeydown = function(event){
        keyCode = event.keyCode;
        
        //37-40 är piltangenternas keycodes
        switch (keyCode) {
        case 13:
            btn.click();
            break;

        case 37:
            if(direction != 'right')
                direction = 'left';
            break;
            
        case 39:
            if(direction != 'left')
                direction = 'right';
            break;
            
        case 38:
            if(direction != 'down')
                direction = 'up';
            break;

        case 40:
            if(direction != 'up')
                direction = 'down';
            break;
        }
        drawModule.paint()
    }
})(window, document, drawModule);
