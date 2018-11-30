<?php

namespace jjbx\kindeditor;

use yii\web\AssetBundle;

class KindEditorAsset extends AssetBundle {
    //put your code here
    public $js=[
        'kindeditor-all-min.js',
        'lang/zh-CN.js',//configure UI language, if you want to use english, then configure it to "lang/en.js"
       // 'kindeditor.js'
    ];
    public $css=[
        'themes/default/default.css'
    ];

    public $jsOptions=[
        'charset'=>'utf8',
    ];


    public function init() {
        //资源所在目录
        $this->sourcePath = dirname(__FILE__) . DIRECTORY_SEPARATOR.'source'. DIRECTORY_SEPARATOR;
    }
}

?>
