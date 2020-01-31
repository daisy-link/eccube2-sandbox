<!--{*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2014 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *}-->

<ul>
    {for $foo=1 to 3}
        <li>{$foo}</li>
    {/for}
</ul>

<!--{strip}-->
<div class="block_outer">
    <div id="news_area">
        <h2>バナー情報</h2>

        <div class="block_body">
            <!--{if is_array($banner)}-->
            <!--{foreach from=$banner item=banner name=myloop}-->
            <!--{if $smarty.foreach.myloop.index < 5 }-->


            <div class="banner_contents">
                <!--{$banner.banner_title|h|nl2br}-->
                <a
                <!--{if $banner.banner_url}--> href="<!--{$banner.banner_url}--> "
                <!--{if $banner.banner_select eq "2"}--> target="_blank"
                <!--{/if}-->
                <!--{/if}-->
                >
                <img src="<!--{$smarty.const.IMAGE_TEMP_URLPATH}--><!--{$banner.main_list_image|sfNoImageMainList|h}-->"
                     alt="<!--{$arrProduct.name|h}-->" class="picture"/>
                </a>
            </div>
            <!--{/if}-->
            <!--{/foreach}-->
            <!--{else}-->
            <!--{$banner}-->
            <!--{/if}-->
        </div>
    </div>
</div>
<!--{/strip}-->
