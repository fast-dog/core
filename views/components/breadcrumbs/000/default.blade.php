<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 04.02.2017
 * Time: 19:08
 */
use \App\Modules\Content\Entity\Content;
$url = \Request::url();

?>
<div class="breadcrumbs">
    <div class="container">
        <? if(isset($item[Content::NAME])):?>
        <h1 class="pull-left">
            <?=$item[Content::NAME]?>
        </h1>
        <? endif;?>
        <? if(isset($path)):?>
        <ul class="pull-right breadcrumb">
            <? foreach ($path as $item) :
            $active = ($item['url'] == $url);?>
            <li <?= $active ? 'class="active"' : ''?>>
                <? if($active):?>
                      <?= $item['name'] ?>
                <? else:?>
                <a href="<?=($item['url'] == '#') ? '/' : $item['url']?>"> <?=$item['name']?></a>
                <? endif;?>
            </li>
            <? endforeach;?>
        </ul>
        <? endif;?>
    </div>
</div>
