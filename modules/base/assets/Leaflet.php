<?php
/**
 * @copyright Copyright (c) 2016 RNP
 * @license http://github.com/ufrgs-hyman/meican#license
 */

namespace meican\base\assets;

use yii\web\AssetBundle;

/**
 * @author Maurício Quatrin Guerreiro
 */
class Leaflet extends AssetBundle
{
    public $sourcePath = '@bower/leaflet/dist';
    
    public $css = [
        'leaflet.css',
    ];
    
    public $js = [
        'leaflet.js',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}

?>