<!DOCTYPE html>
<html>
 
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css?family=M+PLUS+Rounded+1c&display=swap" rel="stylesheet">

    <title>Live Captions</title>

    <style type="text/css">
        @font-face{
        font-family:"07NikumaruFont";
        src: url("https://www.sayonari.com/font/07NikumaruFont.woff") format("woff");
        }

        button, input, select, textarea {
            /* font-family : inherit; */
            /* font-family: 'メイリオ', Meiryo,sans-serif; */
            /* font-size   : 300%; */
            /* color  : black; */
            font-weight : 0;
            text-align  : center;       /* left, center, right */
            vertical-align : top;    /* top, middle, bottom */
            -webkit-text-stroke-color: rgb(21, 0, 141);
            -webkit-text-stroke-width: 0px;
        }

        html {
            height: 100%;
            width: 100%;
        }

        body {
            height: 100%;
            width: 100%;
            margin: 0;
            font-family: 'M PLUS Rounded 1c', sans-serif;
            /* font-family:'07NikumaruFont'; */
        }
        table {
            width: 100%;
            /* table-layout: fixed; */
        }
        table.btm_table {
            position:absolute;
            /* bottom:0; */
        }

        table td {
            /*word-break: break-all;*/
            overflow-wrap : break-word;
        }
    </style>

    <style>
        /* prepare the selectors to add a stroke to */

        .stroke-single-imb{
            /* position: absolute; */
            left: 0;
            right: 0;
            margin: 0;
            /* -webkit-text-stroke: 0px #0000FF;  */
        }

        .stroke-single-bg{
            position: absolute;
            left: 0;
            right: 0;
            margin: auto;
            /* -webkit-text-stroke: 3px #FF0000;  */
        }
        /* add a single stroke */
        .stroke-single-fg{
            position: absolute;
            left: 0;
            right: 0;
            margin: auto;
            /* -webkit-text-stroke: 0px #FFFFFF; */
        }

    </style>

    <script>
        var flag_speech = 0;
 
        // windowを読み込んだら音声認識が開始される --------------
        window.onload = function(){
            vr_function();
        }

        
        // URLパラメータ取得用関数 ---------------------------
        function getParam(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, "\\$&");
            var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, " "));
        }

        function vr_function() {
            window.SpeechRecognition = window.SpeechRecognition || webkitSpeechRecognition;
            // 音声認識用設定 ----------------------
            var recognition = new webkitSpeechRecognition();
            recognition.lang = 'cmn-Hant-TW';
            recognition.interimResults = true;
            recognition.continuous = false;
            var recog_text = '';
            var trans_text = '';

            // 翻訳用設定 ---------------------------
            var trans_sourcelang = 'ja';
            var trans_destlang = 'en';

            var gas_key = getParam('gas_key');
            
            var TRANS_URL = 'https://script.google.com/macros/s/' + gas_key + '/exec';
            var query = ''

            ///////////////////////////////////////////////////////////
            // 各種イベントへの対応 ---------------------------------
            recognition.onsoundstart = function(){
                // document.getElementById('status').innerHTML = "認識中";
            };
            recognition.onnomatch = function(){
                // document.getElementById('status').innerHTML = "もう一度試してください";
            };
            recognition.onerror= function(){
                // document.getElementById('status').innerHTML = "エラー";
                vr_function();
            };
            recognition.onsoundend = function(){
                // document.getElementById('status').innerHTML = "停止中";
                recognition.stop()
                vr_function();
            };
            recognition.onspeechend = function(){
                // document.getElementById('status').innerHTML = "停止中";
                recognition.stop()
                vr_function();
            };

            //////////////////////////////////////////////////////////
            // URLからの値読み込み -------------------
            arg_recog = getParam('recog');
            arg_trans = getParam('trans');

            // 言語設定 ----------------------------
            if (arg_recog != null) {
                recognition.lang = arg_recog;
                trans_sourcelang = recognition.lang;
            }
            if (arg_trans != null) {
                trans_destlang = arg_trans;
            }
            
            if (trans_sourcelang == trans_destlang){
                alert("ERROR! Please set different language between recog and trans!\nYou set both [" + trans_sourcelang + "]!");
            }

            /////////////////////////////////////////////////////////
            // API用設定 ---------------------------
            var request = new XMLHttpRequest();
            
            var num_characters = Math.floor(window.innerWidth/87);
            //text size to width per character
            //50 - 69
            //60 - 80
            //65 - 87

            // 認識結果が返ってきたとき ------------------
            recognition.onresult = function(event) {
                var results = event.results;
                //console.log(results);
                for (var i = event.resultIndex; i < results.length; i++) {
                    recog_text = results[i][0].transcript;
                    recog_text = recog_text.replace(/[A-Za-z]/g, '');

                    if (results[i].isFinal)
                    {
                        //-(1*num_characters)-results[i][0].transcript.length%num_characters
                        //1 row of characters "history" + moving last line. 
                        //adjust to only 1 moving last line? 

                        //remove english characters
                        

                        if (recog_text.length%num_characters==0) {
                            recog_text = recog_text.substr(-num_characters*2);
                        }
                        else {
                            recog_text = recog_text.substr(-num_characters*1-recog_text.length%num_characters);
                        }

                        document.getElementById('speech_text-imb').innerHTML = recog_text;
                        document.getElementById('speech_text-bg').innerHTML = recog_text;
                        document.getElementById('speech_text-fg').innerHTML = recog_text;

                        if(gas_key != null){
                            query = TRANS_URL + '?text=' + recog_text + '&source=' + trans_sourcelang + '&target=' + trans_destlang;
                            request.open('GET', query, true);

                            request.onreadystatechange = function(){
                                if (request.readyState === 4 && request.status === 200){
                                    document.getElementById('speech_text-imb').innerHTML = recog_text;
                                    document.getElementById('speech_text-bg').innerHTML = recog_text;
                                    document.getElementById('speech_text-fg').innerHTML = recog_text;
                                    document.getElementById('trans_text-imb').innerHTML = request.responseText;
                                    document.getElementById('trans_text-bg').innerHTML = request.responseText;
                                    document.getElementById('trans_text-fg').innerHTML = request.responseText;
                                }
                                //vr_function();
                            }
                            request.send(null);
                        } else {
                            document.getElementById('speech_text-imb').innerHTML = recog_text;     
                            document.getElementById('speech_text-bg').innerHTML = recog_text;
                            document.getElementById('speech_text-fg').innerHTML = recog_text;           
                            vr_function();
                        }
                    }
                    else
                    {
                        if (recog_text.length%num_characters==0) {
                            recog_text = recog_text.substr(-num_characters*2);
                        }
                        else {
                            recog_text = recog_text.substr(-num_characters*1-recog_text.length%num_characters);
                        }
                        document.getElementById('speech_text-imb').innerHTML = recog_text;
                        document.getElementById('speech_text-bg').innerHTML = recog_text;
                        document.getElementById('speech_text-fg').innerHTML = recog_text;
                        flag_speech = 1;
                    }
                }
            }
            flag_speech = 0;
            recognition.start();
        }
    </script> 
</head>
 





<body>
    <div class="big" id="result_text">
        <table id="text_table" class="btm_table">
            <tr><td id="tbl_td" align="left" valign='bottom'>
                <div class="stroke-single-bg" id="speech_text-bg">
                    [Waiting...]
                </div> 
                <div class="stroke-single-fg" id="speech_text-fg">
                    [Waiting...]
                </div>
                <div class="stroke-single-imb" id="speech_text-imb">
                    [Waiting...]
                </div> 

                <div class="stroke-single-bg" id="trans_text-bg">
                    
                </div>  
                <div class="stroke-single-fg" id="trans_text-fg">
                    
                </div>  
                <div class="stroke-single-imb" id="trans_text-imb">
                    
                </div>  
            </td></tr>
        </table>
    </div>
</body>





<!-- ############## 末尾のjavascript ############## -->
<script type="text/javascript">

// 表示スタイル変更 ---------------------------------
if (getParam('bgcolor') != null){
    document.bgColor = getParam('bgcolor');
}

if (getParam('v_align') == "top"){
    document.getElementById("text_table").style.bottom = -1;
} else if(getParam('v_align') == "bottom"){
    document.getElementById("text_table").style.bottom = 0;
}

// 高さ合わせ用フォント（色を背景色と同じにする）
if (getParam('bgcolor') != null){
    document.getElementById("speech_text-imb").style.webkitTextStrokeColor = getParam('bgcolor');
}

if (getParam('st_width') != null){
    document.getElementById("speech_text-imb").style.webkitTextStrokeWidth = getParam('st_width') + 'pt';
}


if (getParam('bgcolor') != null){
    document.getElementById("trans_text-imb").style.webkitTextStrokeColor = getParam('bgcolor');
}

if (getParam('st_width') != null){
    document.getElementById("trans_text-imb").style.webkitTextStrokeWidth = getParam('st_width') + 'pt';
}


// 音声認識結果テキスト
if (getParam('font') != null){
    document.getElementById("result_text").style.fontFamily = "'" + getParam('font') + "'";
}

if (getParam('size') != null){
    document.getElementById("result_text").style.fontSize = getParam('size') + 'pt';
}

if (getParam('color') != null){
    document.getElementById("speech_text-fg").style.color = getParam('color');
}

if (getParam('st_color') != null){
    document.getElementById("speech_text-bg").style.webkitTextStrokeColor = getParam('st_color');
}

if (getParam('weight') != null){
    document.getElementById("result_text").style.fontWeight = getParam('weight');
}

if (getParam('st_width') != null){
    document.getElementById("speech_text-bg").style.webkitTextStrokeWidth = getParam('st_width') + 'pt';
}


// 翻訳結果テキスト
if (getParam('color') != null){
    document.getElementById("trans_text-fg").style.color = getParam('color');
}

if (getParam('st_color') != null){
    document.getElementById("trans_text-bg").style.webkitTextStrokeColor = getParam('st_color');
}

if (getParam('st_width') != null){
    document.getElementById("trans_text-bg").style.webkitTextStrokeWidth = getParam('st_width') + 'pt';
}




// font-family: 'メイリオ', Meiryo,sans-serif;
//             font-size   : 300%;
//             color  : blue;
//             font-weight : 900;
//             text-align  : center;       /* left, center, right */
//             vertical-align : bottom;    /* top, middle, bottom */
//             -webkit-text-stroke-color: rgb(28, 141, 0);
//             -webkit-text-stroke-width: 0px;

// URLパラメータ取得用関数 ---------------------------
function getParam(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}
</script>






</html>
