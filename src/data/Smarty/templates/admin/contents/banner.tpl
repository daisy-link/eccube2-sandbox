<!--{*
/*
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
 */
*}-->

<script type="text/javascript">
    // 表示非表示切り替え
    function lfDispSwitch(id){
        var obj = document.getElementById(id);
        if (obj.style.display == 'none') {
            obj.style.display = '';
        } else {
            obj.style.display = 'none';
        }
    }



    // セレクトボックスのリストを移動
    // (移動元セレクトボックスID, 移動先セレクトボックスID)
    function fnMoveSelect(select, target) {
        $('#' + select).children().each(function() {
            if (this.selected) {
                $('#' + target).append(this);
                $(this).attr({selected: false});
            }
        });
        // IE7再描画不具合対策
        var ua = navigator.userAgent.toLowerCase();
        if (ua.indexOf("msie") != -1 && ua.indexOf('msie 6') == -1) {
            $('#' + select).hide();
            $('#' + select).show();
            $('#' + target).hide();
            $('#' + target).show();
        }
    }

    // target の子要素を選択状態にする
    function selectAll(target) {
        $('#' + target).children().prop('selected', 'selected');
    }

    // 商品種別によってダウンロード商品のフォームの表示非表示を切り替える
    function toggleDownloadFileForms(value) {
        if (value == '2') {
            $('.type-download').show('fast');
        } else {
            $('.type-download').hide('fast');
        }
    }

    $(function(){
        var form_product_type = $('input[name=product_type_id]');
        form_product_type.click(function(){
            toggleDownloadFileForms(form_product_type.filter(':checked').val());
        });
        toggleDownloadFileForms(form_product_type.filter(':checked').val());
    })
</script>

<div id="admin-contents" class="contents-main">
    <form name="form1" id="form1" method="post" action="?" enctype="multipart/form-data">
        <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
        <input type="hidden" name="mode" value="edit" />
        <input type="hidden" name="image_key" value="" />
        <input type="hidden" name="id" value="<!--{$arrForm.id.value|default:$tpl_banner_id|h}-->" />
        <input type="hidden" name="save_main_list_image" value="<!--{$arrForm.save_main_list_image.value|h}-->" />

        <!--{foreach key=key item=item from=$arrForm.arrHidden}-->
        <input type="hidden" name="<!--{$key}-->" value="<!--{$item|h}-->" />
        <!--{/foreach}-->


        <!--{* ▼登録テーブルここから *}-->
        <table>

            <tr>
                <th>タイトル<span class="attention"> *</span></th>
                <td>
                    <!--{if $arrErr.banner_title}--><span class="attention"><!--{$arrErr.banner_title}--></span><!--{/if}-->
                    <textarea name="banner_title" cols="60" rows="2" class="area62" maxlength="<!--{$arrForm.banner_title.length}-->" <!--{if $arrErr.banner_title}-->style="background-color:<!--{$smarty.const.ERR_COLOR|h}-->"<!--{/if}-->><!--{"\n"}--><!--{$arrForm.banner_title.value|h}--></textarea><br />
                    <span class="attention"> (上限<!--{$arrForm.banner_title.length}-->文字)</span>
                </td>
            </tr>
            <tr>
                <th>URL</th>
                <td>
                    <span class="attention"><!--{$arrErr.banner_url}--></span>
                    <input type="text" name="banner_url" size="60" class="box60"    value="<!--{$arrForm.banner_url.value|h}-->" <!--{if $arrErr.banner_url}-->style="background-color:<!--{$smarty.const.ERR_COLOR|h}-->"<!--{/if}--> maxlength="<!--{$arrForm.banner_url.length}-->" />
                    <span class="attention"> (上限<!--{$arrForm.banner_url.length}-->文字)</span>
                </td>
            </tr>
            <tr>
                <th>リンク先URL</th>
                <td><label><input type="checkbox" name="banner_select" value="2" <!--{if $arrForm.banner_select.value eq 2}--> checked <!--{/if}--> /> 別ウィンドウで開く</label></td>
            </tr>
            <tr>
                <!--{assign var=key value="main_list_image"}-->
                <th>一覧-メイン画像<br />[<!--{$smarty.const.SMALL_IMAGE_WIDTH}-->×<!--{$smarty.const.SMALL_IMAGE_HEIGHT}-->]</th>
                <td>
                    <a name="<!--{$key}-->"></a>
                    <a name="main_image"></a>
                    <a name="main_large_image"></a>
                    <span class="attention"><!--{$arrErr[$key]}--></span>
                    <!--{if $arrForm.arrFile[$key].filepath != ""}-->
                    <img src="<!--{$arrForm.arrFile[$key].filepath}-->" alt="<!--{$arrForm.name|h}-->" />　<a href="" onclick=" eccube.setModeAndSubmit('delete_image', 'image_key', '<!--{$key}-->'); return false;">[画像の取り消し]</a><br />
                    <!--{/if}-->
                    <input type="file" name="main_list_image" size="40" style="<!--{$arrErr[$key]|sfGetErrorColor}-->" />
                    <a class="btn-normal" href="javascript:;" name="btn" onclick="; eccube.setModeAndSubmit('upload_image', 'image_key', '<!--{$key}-->'); return false;">アップロード</a>
                </td>
                </th>
            </tr>
            <tr>
                <th>テキスト</th>
                <td>
                    <!--{if $arrErr.banner_text}--><span class="attention"><!--{$arrErr.banner_text}--></span><!--{/if}-->
                    <textarea name="banner_text" cols="60" rows="8" wrap="soft" class="area60" maxlength="<!--{$arrForm.banner_text.length}-->" style="background-color:<!--{if $arrErr.banner_text}--><!--{$smarty.const.ERR_COLOR|h}--><!--{/if}-->"><!--{"\n"}--><!--{$arrForm.banner_text.value|h}--></textarea><br />
                    <span class="attention"> (上限3000文字)</span>
                </td>
            </tr>
        </table>
        <!--{* ▲登録テーブルここまで *}-->

        <div class="btn-area">
            <ul>
                <li><a class="btn-action" href="javascript:;" onclick="eccube.fnFormModeSubmit('form1', 'edit', '', ''); return false;"><span class="btn-next">この内容で登録する</span></a></li>
            </ul>
        </div>
    </form>

    <h2>新着情報一覧
        <a class="btn-normal" href="">新規登録</a>
    </h2>

    <!--{if $arrErr.moveposition}-->
    <p><span class="attention"><!--{$arrErr.moveposition}--></span></p>
    <!--{/if}-->
    <!--{* ▼一覧表示エリアここから *}-->
    <form name="move" id="move" method="post" action="?">
        <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
        <input type="hidden" name="mode" value="moveRankSet" />
        <input type="hidden" name="id" value="" />
        <input type="hidden" name="moveposition" value="" />
        <input type="hidden" name="rank" value="" />
        <table class="list">
            <col width="10%" />

            <col width="55%" />
            <col width="5%" />
            <col width="5%" />
            <col width="25%" />
            <tr>
                <th>表示順</th>
                <th>タイトル</th>
                <th class="edit">編集</th>
                <th class="delete">削除</th>
                <th>移動</th>
            </tr>
            <!--{section name=data loop=$arrBanner}-->
            <tr style="background:<!--{if $arrBanner[data].id != $tpl_banner_id}-->#ffffff<!--{else}--><!--{$smarty.const.SELECT_RGB}--><!--{/if}-->;" class="center">
                <!--{assign var=db_rank value="`$arrBanner[data].rank`"}-->
                <td><!--{math equation="$line_max - $db_rank + 1"}--></td>

                <td class="left">
                    <!--{if $arrBanner[data].banner_select eq 1 && $arrBanner[data].banner_url != ""}--><a href="<!--{$arrBanner[data].banner_url|h}-->" ><!--{$arrBanner[data].banner_title|h|nl2br}--></a>
                    <!--{elseif $arrBanner[data].banner_select eq 1 && $arrBanner[data].banner_url == ""}--><!--{$arrBanner[data].banner_title|h|nl2br}-->
                    <!--{elseif $arrBanner[data].banner_select eq 2 && $arrBanner[data].banner_url != ""}--><a href="<!--{$arrBanner[data].banner_url|h}-->" target="_blank" ><!--{$arrBanner[data].banner_title|h|nl2br}--></a>
                    <!--{else}--><!--{$arrBanner[data].banner_title|h|nl2br}-->
                    <!--{/if}-->
                </td>
                <td>
                    <!--{if $arrBanner[data].id != $tpl_banner_id}-->
                    <a href="#" onclick="eccube.fnFormModeSubmit('move','pre_edit','id','<!--{$arrBanner[data].id|h}-->'); return false;">編集</a>
                    <!--{else}-->
                    編集中
                    <!--{/if}-->
                </td>
                <td><a href="#" onclick="eccube.fnFormModeSubmit('move','delete','id','<!--{$arrBanner[data].id|h}-->'); return false;">削除</a></td>
                <td>
                    <!--{if count($arrBanner) != 1}-->
                    <input type="text" name="pos-<!--{$arrBanner[data].id|h}-->" size="3" class="box3" />番目へ<a href="?" onclick="eccube.fnFormModeSubmit('move', 'moveRankSet','id', '<!--{$arrBanner[data].id|h}-->'); return false;">移動</a><br />
                    <!--{/if}-->
                    <!--{if $smarty.section.data.iteration != 1}-->
                    <a href="?" onclick="eccube.fnFormModeSubmit('move','up','id','<!--{$arrBanner[data].id|h}-->'); return false;">上へ</a>
                    <!--{/if}-->
                    <!--{if !$smarty.section.data.last}-->
                    <a href="?" onclick="eccube.fnFormModeSubmit('move','down','id','<!--{$arrBanner[data].id|h}-->'); return false;">下へ</a>
                    <!--{/if}-->
                </td>
            </tr>
            <!--{sectionelse}-->
            <tr class="center">
                <td colspan="6">現在データはありません。</td>
            </tr>
            <!--{/section}-->
        </table>
    </form>
    <!--{* ▲一覧表示エリアここまで *}-->

</div>
