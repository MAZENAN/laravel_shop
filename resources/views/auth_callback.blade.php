<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script>
    function GetRequest() {
        var url = location.search; //获取url中"?"符后的字串
        var theRequest = {};
        if (url.indexOf("?") !== -1) {
            var str = url.substr(1);
            strs = str.split("&");
            for (var i = 0; i < strs.length; i++) {
                theRequest[strs[i].split("=")[0]] = decodeURI(strs[i].split("=")[1]);
            }
        }
        return theRequest;
    }

    //调用
    var Request = GetRequest();

    if (Request['error']) {
        // 用户未授权处理
        alert(Request['error']);
    }else
    {
        var code = Request['code'];
        var state = Request['state'];

        axios.post('/get/token', {
            params: {
                code,
                state
            }
        })
            .then(function (response) {
                console.log(response.data);
            });
    }
</script>