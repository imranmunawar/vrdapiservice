<!DOCTYPE html>
<head>
    <title>Interview Room</title>
    <meta charset="utf-8" />
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/1.7.8/css/bootstrap.css"/>
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/1.7.8/css/react-select.css"/>
    <meta name="format-detection" content="telephone=no">
</head>
<body>
<script src="https://source.zoom.us/1.7.8/lib/vendor/react.min.js"></script>
<script src="https://source.zoom.us/1.7.8/lib/vendor/react-dom.min.js"></script>
<script src="https://source.zoom.us/1.7.8/lib/vendor/redux.min.js"></script>
<script src="https://source.zoom.us/1.7.8/lib/vendor/redux-thunk.min.js"></script>
<script src="https://source.zoom.us/1.7.8/lib/vendor/jquery.min.js"></script>
<script src="https://source.zoom.us/1.7.8/lib/vendor/lodash.min.js"></script>

<script src="https://source.zoom.us/zoom-meeting-1.7.8.min.js"></script>
<!-- <script src="js/zoom.js"></script> -->

<script>
(function(){

    console.log('checkSystemRequirements');
    console.log(JSON.stringify(ZoomMtg.checkSystemRequirements()));

    // it's option if you want to change the WebSDK dependency link resources.
    // ZoomMtg.setZoomJSLib('https://source.zoom.us/1.7.8/lib', '/av'); // CDN version default
    // ZoomMtg.setZoomJSLib('https://jssdk.zoomus.cn/1.7.8/lib', '/av'); // china cdn option
    // ZoomMtg.setZoomJSLib('http://localhost:9999/node_modules/@zoomus/websdk/dist/lib', '/av'); // Local version default

    ZoomMtg.preLoadWasm();

    ZoomMtg.prepareJssdk();

    var API_KEY = '{{ env("ZOOM_API_KEY") }}';

    /**
     * NEVER PUT YOUR ACTUAL API SECRET IN CLIENT SIDE CODE, THIS IS JUST FOR QUICK PROTOTYPING
     * The below generateSignature should be done server side as not to expose your api secret in public
     * You can find an eaxmple in here: https://marketplace.zoom.us/docs/sdk/native-sdks/Web-Client-SDK/tutorial/generate-signature
     */
    var API_SECRET = '{{ env("ZOOM_API_SECRET") }}';

    $(document).ready(function(){

        // e.preventDefault();
        //
        // if(!this.form.checkValidity()){
        //     alert("Enter Name and Meeting Number");
        //     return false;
        // }

        var meetConfig = {
            apiKey: API_KEY,
            apiSecret: API_SECRET,
            meetingNumber: parseInt({{ $interview_id }}),
            userName: "{{ $name }}",
            passWord: "{{ $password }}",
            email: "{{ $email }}",
            leaveUrl: "https://zoom.us",
            role: 1
        };

        var signature = ZoomMtg.generateSignature({
            meetingNumber: meetConfig.meetingNumber,
            apiKey: meetConfig.apiKey,
            apiSecret: meetConfig.apiSecret,
            role: meetConfig.role,
            success: function(res){
                console.log(res.result);
            }
        });

        ZoomMtg.init({
            leaveUrl: 'https://www.zoom.us',
            success: function () {
                ZoomMtg.join(
                    {
                        meetingNumber: meetConfig.meetingNumber,
                        userName: meetConfig.userName,
                        signature: signature,
                        apiKey: meetConfig.apiKey,
                        userEmail: meetConfig.email,
                        passWord: meetConfig.passWord,
                        success: function(res){
                            $('#nav-tool').hide();
                            console.log('join meeting success');
                        },
                        error: function(res) {
                            console.log(res);
                        }
                    }
                );
            },
            error: function(res) {
                console.log(res);
            }
        });
    });
})();
</script>
</body>
</html>
