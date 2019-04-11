<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>海报</title>
    <meta
            name="viewport"
            content="width=device-width, maximum-scale=1.0, initial-scale=1.0, user-scalable=0"
    />
    <style>
        body,
        div,
        * {
            padding: 0;
            margin: 0;
        }
        body {background: #000;font-size: 14px;color: #666;}
        .page {background: #fff;width: 100%;height: 100%;position: absolute;margin-left: 0%;overflow: hidden;}
        .img {}
        .img img {width: 100%;display: block;}
        .title {padding: 20px 10px;line-height: 20px;font-size: 16px;color: #333;}
        .line {border-top: 1px dashed #ccc;margin: 0px 0px;position: relative;}
        .line::before,.line::after{content: "";background: #ccc;width: 10px;height: 10px;border-radius:100em ;left: -5px;top:-5px;position:absolute;}
        .line::after{right: -5px;left: auto;}
        .bottomInfo{text-align: center;padding: 30px 0;}
        .eqCode {width: 100px;height: 100px;vertical-align: middle;margin-right: 20px;margin: 0px auto;margin-bottom: 10px;}
        .eqCode img{width: 100px;height: 100px;text-align: center;overflow: hidden;}
        .txt {display: inline-block;line-height: 24px;vertical-align: middle;}
    </style>
</head>

<body>
<div class="page">
    <div class="img">
        <img
                src="{$pic}"
                alt="视频封面"
        />
    </div>
    <div class="title">
        {$title}
    </div>
    <div class="line"></div>

    <div class="bottomInfo">
        <div class="eqCode">
            <!-- <img
              src="http://pic.aiyingli.com/wp-content/uploads/2017/06/105825h9a88wwdfsxhxkw3.jpg"
              alt="二维码"
            /> -->
        </div>
        <div class="txt">
            长按识别小程序<br>
            前往查看
        </div>
    </div>
</div>
</body>
</html>
