<?php
/**
 * Created by PhpStorm.
 * User: dg
 * Date: 06.02.2017
 * Time: 23:25
 */
$lang = config('app.locale');
$languages = config('app.languages');
?>
<li class="hoverSelector">
    <i class="fa fa-globe"></i>
    <a href="javascript:void(0)">
        <?=trans('public.Язык')?>
    </a>
    <ul class="languages hoverSelectorBlock">
        <? foreach ($languages as $id=>$item): ?>
        <li <?=($lang === $id) ? ' class="active" ' : ''?>>
            <a href="#"><?=$item?>
                <?=($lang === $id) ? '<i class="fa fa-check"></i>' : ''?>
            </a>
        </li>
        <? endforeach;?>
    </ul>
</li>