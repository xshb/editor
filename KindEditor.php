<?php


namespace jjbx\kindeditor;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\InputWidget;

class KindEditor extends InputWidget {

    //配置选项，参阅KindEditor官网文档(定制菜单等)
    public $clientOptions = [];
    //定义编辑器的类型，
    //默认为textEditor;
    //uploadButton：自定义上传按钮

    //colorpicker:取色器
    //file-manager浏览服务器
    //image-dialog 上传图片
    //fileDialog 文件上传

   //multiImageDialog批量上传图片 *
   //dialog:弹窗 *
    private $arr = array(
      'uploadButton'=> '上传',
      'colorpicker'=> '打开取色器',
      'file-manager'=> '浏览服务器',
      'image-dialog'=> '选择图片',
      'uploadButton'=> '选择文件'
    );

    public $editorType;

    public $toId;
    public $id = '';

   //默认配置
   protected $_options;
   protected $btn_id;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init() {
        $this->id = empty($this->id) ? Html::getInputId($this->model, $this->attribute) : $this->id;
        $this->btn_id = $this->editorType .'_' .$this->id;
        $this->_options = [
            'fileManagerJson' => Url::to(['Kupload', 'action' => 'fileManagerJson']),
            'uploadJson' => Url::to(['Kupload', 'action' => 'uploadJson']),
            'width' => '100%',
            'height' => '300',
            //'langType' => (strtolower(Yii::$app->language) == 'en-us') ? 'en' : 'zh_cn',//kindeditor支持一下语言：en,zh_CN,zh_TW,ko,ar
        ];
        $this->clientOptions = ArrayHelper::merge($this->_options, $this->clientOptions);
        parent::init();
    }
    public function run() {
        $this->registerClientScript();
        $inpt_html = $btn_html = '';
        if ($this->hasModel()) {
            if(empty($this->editorType)){
              $inpt_html = Html::activeTextarea($this->model, $this->attribute, ['id' => $this->id]);
              $btn_html = '';
            }else{
              $inpt_html = Html::activeInput('text', $this->model, $this->attribute, ['id' => $this->id,'class'=>'wx_kind_editor_input']);
              $btn_html =  Html::input('button', '', $this->arr[ $this->editorType], ['id' => $this->btn_id,'class'=>'wx_kind_editor_btn']);
            }
        } else {
          if(empty($this->editorType)){
            $inpt_html = Html::textarea($this->id, $this->value, ['id' => $this->id]);
            $btn_html = '';
          }else{
            $inpt_html = Html::input('text', $this->id, $this->value, ['id' => $this->id,'class'=>'wx_kind_editor_input']) ;
            $btn_html =  Html::input('button', '', $this->arr[ $this->editorType], ['id' => $this->btn_id,'class'=>'wx_kind_editor_btn']);
          }
        }

      return $inpt_html . $btn_html;
    }

    /**
     * 注册客户端脚本
     */
    protected function registerClientScript() {
        //UEditorAsset::register($this->view);
        KindEditorAsset::register($this->view);
        $clientOptions = Json::encode($this->clientOptions);

        $fileManagerJson = Url::to(['Kupload', 'action' => 'fileManagerJson']);
        $uploadJson = Url::to(['Kupload', 'action' => 'uploadJson']);
        switch ($this->editorType) {
            case 'uploadButton':
                $url = Url::to(['Kupload', 'action' => 'uploadJson', 'dir' => 'file']);

                $script = <<<EOT
                             KindEditor.ready(function(K) {
				var uploadbutton = K.uploadbutton({
					button : K('#{$this->btn_id}'),
					fieldName : 'imgFile',
                                        url : '{$url}',
					afterUpload : function(data) {
						if (data.error === 0) {
							var url = K.formatUrl(data.url, 'absolute');
							K('#{$this->id}').val(url);
						} else {
							alert(data.message);
						}
					},
					afterError : function(str) {
						alert('自定义错误信息: ' + str);
					}
				});
				uploadbutton.fileBox.change(function(e) {
					uploadbutton.submit();
				});
			});
EOT;

                break;
            case 'colorpicker':
                $script = <<<EOT
                            KindEditor.ready(function(K) {
				var colorpicker;
				K('#{$this->btn_id}').bind('click', function(e) {
					e.stopPropagation();
					if (colorpicker) {
						colorpicker.remove();
						colorpicker = null;
						return;
					}
					var colorpickerPos = K('#{$this->btn_id}').pos();
					colorpicker = K.colorpicker({
						x : colorpickerPos.x,
						y : colorpickerPos.y + K('#colorpicker').height(),
						z : 19811214,
						selectedColor : 'default',
						noColor : '无颜色',
						click : function(color) {
							K('#{$this->id}').val(color);
							colorpicker.remove();
							colorpicker = null;
						}
					});
				});
				K(document).click(function() {
					if (colorpicker) {
						colorpicker.remove();
						colorpicker = null;
					}
				});
			});
EOT;

                break;
            case 'file-manager':
                $script = <<<EOT
                           KindEditor.ready(function(K) {
				var editor = K.editor({

					fileManagerJson : '{$fileManagerJson}'
				});
				K('#{$this->btn_id}').click(function() {
					editor.loadPlugin('filemanager', function() {
						editor.plugin.filemanagerDialog({
							viewType : 'VIEW',
							dirName : 'image',
							clickFn : function(url, title) {
								K('#{$this->id}').val(url);
								editor.hideDialog();
							}
						});
					});
				});
			});
EOT;

                break;
            case 'image-dialog':
								$s = empty($this->toId)?'':"K.appendHtml('#{$this->toId}', \"<img src='\"+url+\"' alt='' />\");";
                $script = <<<EOT
   KindEditor.ready(function(K) {
				var editor = K.editor({
					allowFileManager : true,
        "uploadJson":"{$uploadJson}",
         "fileManagerJson":"{$fileManagerJson}",
				});
				K('#{$this->btn_id}').click(function() {
					editor.loadPlugin('image', function() {
						editor.plugin.imageDialog({
							imageUrl : K('#{$this->id}').val(),
							clickFn : function(url, title, width, height, border, align) {
								K('#{$this->id}').val(url);
								{$s}
								editor.hideDialog();
							}
						});
					});
				});
	 });
EOT;

                break;
            case 'file-dialog':
                $script = <<<EOT
                          KindEditor.ready(function(K) {
				var editor = K.editor({
					allowFileManager : true,
                                        "uploadJson":"{$uploadJson}",
                                         "fileManagerJson":"{$fileManagerJson}",

				});
				K('#{$this->btn_id}').click(function() {
					editor.loadPlugin('insertfile', function() {
						editor.plugin.fileDialog({
							fileUrl : K('#{$this->id}').val(),
							clickFn : function(url, title) {
								K('#{$this->id}').val(url);
								editor.hideDialog();
							}
						});
					});
				});
			});
EOT;

                break;
            default:
                $script = "KindEditor.ready(function(K) {
	K.create('#" . $this->id . "', " . $clientOptions . ");
});";
                break;
        }

        $this->view->registerJs($script, View::POS_READY);
    }

}

?>
