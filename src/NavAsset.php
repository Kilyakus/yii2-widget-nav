<?php
namespace kilyakus\nav;

class NavAsset extends \kilyakus\widgets\AssetBundle
{
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['css/nav'],'widget-nav');
        parent::init();
    }
}
