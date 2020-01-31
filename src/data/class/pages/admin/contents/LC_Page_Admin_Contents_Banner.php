<?php
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

require_once CLASS_EX_REALDIR . 'page_extends/admin/LC_Page_Admin_Ex.php';

/**
 * コンテンツ管理 のページクラス.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_Admin_Contents_Banner extends LC_Page_Admin_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */

    public function init()
    {
        parent::init();
//        $this->tpl_mainpage = 'contents/index.tpl';
        $this->tpl_mainpage = 'contents/banner.tpl';
//        $this->tpl_subno = 'index';
        $this->tpl_subno = 'banner';
        $this->tpl_mainno = 'contents';
//        $this->arrForm = array(
//            'year' => date('Y'),
//            'month' => date('n'),
//            'day' => date('j'),
//        );
        $this->tpl_maintitle = 'コンテンツ管理';
//        $this->tpl_subtitle = '新着情報管理';
        $this->tpl_subtitle = 'バナー管理';
        //---- 日付プルダウン設定
//        $objDate = new SC_Date_Ex(ADMIN_NEWS_STARTYEAR);
//        $this->arrYear = $objDate->getYear();
//        $this->arrMonth = $objDate->getMonth();
//        $this->arrDay = $objDate->getDay();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    public function action()
    {
        $objImage = new SC_Image();
        $objBanner = new SC_Helper_Banner_Ex();

        $objFormParam = new SC_FormParam_Ex();
        $this->lfInitParam($objFormParam);
        $objFormParam->setParam($_POST);
        $objFormParam->convParam();

        $banner_id = $objFormParam->getValue('id');

        //---- 新規登録/編集登録
        $mode = $this->getMode();
        $objUpFile = new SC_UploadFile_Ex(IMAGE_TEMP_REALDIR, IMAGE_SAVE_REALDIR);
        $this->lfInitFile($objUpFile);
        $objUpFile->setHiddenFileList($_POST);
        switch ($mode) {
            case 'edit':
                $this->arrErr = $this->lfCheckError($objFormParam);
                if (!SC_Utils_Ex::isBlank($this->arrErr['id'])) {
                    trigger_error('', E_USER_ERROR);

                    return;
                }

                if (count($this->arrErr) <= 0) {
                    // POST値の引き継ぎ
                    $arrParam = $objFormParam->getHashArray();
                    $banner = $objBanner->getBanner($arrParam['id']);
                    $arrParam['main_list_image'] = $banner['main_list_image'];
//                    $save_dir = $_SERVER['DOCUMENT_ROOT'] . '/html/upload/save_image/';
                    // 登録実行
                    $res_banner_id = $this->doRegist($banner_id, $arrParam, $objBanner);
                    if ($res_banner_id !== false) {
                        // 完了メッセージ
                        $banner_id = $res_banner_id;
                        $this->tpl_onload = "alert('登録が完了しました。');";

                        if($mode === 'edit'){
                            //1, tmp_dir['tmp_dir => '/var/www/html/html/upload/temp_image/' 2, id]
                            $this->lfSaveUploadFiles($objUpFile, $banner_id);
                        }

                    }
                }
                // POSTデータを引き継ぐ
                $this->tpl_banner_id = $banner_id;

                break;

            case 'pre_edit':

//                $this->lfInitFormParam_UploadImage($objFormParam);

//                $arrForm = $objFormParam->getFormParamList();

                $banner = $objBanner->getBanner($banner_id);
//

                $objFormParam->setParam($banner);

                // POSTデータを引き継ぐ
                $this->tpl_banner_id = $banner_id;
//                $arrForm = $objFormParam->getFormParamList();
                break;

            case 'delete':
                //----　データ削除
                $objBanner->deleteBanner($banner_id);

                $arrParam = $objFormParam->getHashArray();
                $banner = $objBanner->getBanner($arrParam['id']);
                $save_image_url = $_SERVER['DOCUMENT_ROOT'] . '/html/upload/save_image/';
                $result = glob($save_image_url . '*');

                //save_image内のデータ削除
                if(in_array($save_image_url . $banner['main_list_image'], $result)){
                    foreach($result as $image){
                        if($image === $save_image_url . $banner['main_list_image']){
                            unlink($image);
                        }
                    }
                }


                //自分にリダイレクト（再読込による誤動作防止）
                SC_Response_Ex::reload();
                break;

            //----　表示順位移動
            case 'up':
                $objBanner->rankUp($banner_id);

                // リロード
                SC_Response_Ex::reload();
                break;

            case 'down':
                $objBanner->rankDown($banner_id);

                // リロード
                SC_Response_Ex::reload();
                break;

            case 'moveRankSet':
                //----　指定表示順位移動
                $input_pos = $this->getPostRank($banner_id);
                if (SC_Utils_Ex::sfIsInt($input_pos)) {
                    $objBanner->moveRank($banner_id, $input_pos);
                }
                SC_Response_Ex::reload();
                break;

            // 画像のアップロード


            default:

                break;
        }

        $this->arrBanner = $objBanner->getList();
        $this->line_max = count($this->arrBanner);

        $this->arrForm = $objFormParam->getFormParamList();

        if($mode === 'pre_edit'){
            if($banner['main_list_image']){
                $this->arrForm['arrFile'] = array('main_list_image', $banner['main_list_image']);
                $this->arrForm['arrFile']['main_list_image']['filepath'] = '/html/upload/save_image/' . $banner['main_list_image'];
            }else{
                $this->arrForm['arrFile']['main_list_image']['filepath'] = '';
            }
            $this->arrForm['arrHidden'] = $banner['main_list_image'];
        }
        if($mode === 'edit'){
            if($arrParam['temp_main_list_image']){
                $this->arrForm['arrFile'] = array('temp_main_list_image', $arrParam['temp_main_list_image']);
                $this->arrForm['arrFile']['main_list_image']['filepath'] = '/html/upload/save_image/' . $arrParam['temp_main_list_image'];
            }elseif($arrParam['main_list_image']){
                $this->arrForm['arrFile'] = array('temp_main_list_image', $arrParam['main_list_image']);
                $this->arrForm['arrFile']['main_list_image']['filepath'] = '/html/upload/save_image/' . $arrParam['main_list_image'];
            }else{
                $this->arrForm['arrFile']['main_list_image']['filepath'] = '';
            }
            $this->arrForm['arrHidden'] = $arrParam['temp_main_list_image'];
        }
//

        switch ($mode) {
            //画像アップ
            case 'upload_image':
            case 'delete_image':
                // パラメーター初期化
                $this->lfInitFormParam_UploadImage($objFormParam);
                $this->lfInitFormParam($objFormParam, $_POST);
                $arrForm = $objFormParam->getFormParamList();

                switch ($mode) {
                    case 'upload_image':
                        // ファイルを一時ディレクトリにアップロード
                        $this->arrErr[$arrForm['image_key']['value']] = $objUpFile->makeTempFile(
                            $arrForm['image_key']['value'],
                            IMAGE_RENAME
                        );
//
                        break;
                    case 'delete_image':
                        // ファイル削除
                        $this->lfDeleteTempFile($objUpFile, $arrForm['image_key']['value']);
                        break;
                    default:
                        break;
                }

                // 入力画面表示設定
                $this->arrForm = $this->lfSetViewParam_InputPage($objUpFile, $arrForm);
//
                $this->arrBanner = $objBanner->getList();
                $this->line_max = count($this->arrBanner);
                $this->tpl_banner_id = $banner_id;


                break;
        }
    }

    /**
     * 入力されたパラメーターのエラーチェックを行う。
     * @param SC_FormParam_Ex $objFormParam
     * @return Array  エラー内容
     */
    public function lfCheckError(&$objFormParam)
    {
        $objErr = new SC_CheckError_Ex($objFormParam->getHashArray());
        $objErr->arrErr = $objFormParam->checkError();
//        $objErr->doFunc(array('日付', 'year', 'month', 'day'), array('CHECK_DATE'));

        return $objErr->arrErr;
    }

    /**
     * パラメーターの初期化を行う
     * @param SC_FormParam_Ex $objFormParam
     */
    public function lfInitParam(&$objFormParam)
    {
        $objFormParam->addParam('id', 'id');
//        $objFormParam->addParam('日付(年)', 'year', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
//        $objFormParam->addParam('日付(月)', 'month', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
//        $objFormParam->addParam('日付(日)', 'day', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam(
            'バナータイトル',
            'banner_title',
            STEXT_LEN,
            'KVa',
            array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'SPTAB_CHECK')
        );
        $objFormParam->addParam('バナーURL', 'banner_url', URL_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam('バナーテキスト', 'banner_text', LTEXT_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam('別ウィンドウで開く', 'banner_select', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('save_main_list_image', 'save_main_list_image', '', '', array());
        $objFormParam->addParam('temp_main_list_image', 'temp_main_list_image', '', '', array());
        $objFormParam->addParam('main_list_image', 'main_list_image', '', '', array());

//        $objFormParam->addParam('save_main_list_image', 'save_main_list_image', '', '', array());
        $objFormParam->addParam('save_main_image', 'save_main_image', '', '', array());
        $objFormParam->addParam('save_main_large_image', 'save_main_large_image', '', '', array());
//        $objFormParam->addParam('temp_main_list_image', 'temp_main_list_image', '', '', array());
        $objFormParam->addParam('temp_main_image', 'temp_main_image', '', '', array());
        $objFormParam->addParam('temp_main_large_image', 'temp_main_large_image', '', '', array());
    }

    /**
     * 登録処理を実行.
     *
     * @param integer $bannet_id
     * @param array $sqlval
     * @param SC_Helper_News_Ex $objNews
     * @return multiple
     */
    public function doRegist($banner_id, $sqlval, SC_Helper_Banner_Ex $objBanner)
    {
        $sqlval['id'] = $banner_id;
        $sqlval['creator_id'] = $_SESSION['member_id'];
        $sqlval['banner_select'] = $this->checkLinkMethod($sqlval['banner_select']);
        if (strlen($sqlval['temp_main_list_image']) > 0) {
            $sqlval['main_list_image'] = $sqlval['temp_main_list_image'];
        }
//        else {
//            $sqlval['main_list_image'] = $sqlval['save_main_list_image'];
//        }

        return $objBanner->saveBanner($sqlval);
    }

    /**
     * データの登録日を返す。
     * @param Array $arrPost POSTのグローバル変数
     * @return string 登録日を示す文字列
     */
    public function getRegistDate($arrPost)
    {
        $registDate = $arrPost['year'] . '/' . $arrPost['month'] . '/' . $arrPost['day'];

        return $registDate;
    }

    /**
     * チェックボックスの値が空の時は無効な値として1を格納する
     * @param int $link_method
     * @return int
     */
    public function checkLinkMethod($link_method)
    {
        if (strlen($link_method) == 0) {
            $link_method = 1;
        }

        return $link_method;
    }

    /**
     * ニュースの日付の値をフロントでの表示形式に合わせるために分割
     * @param String $news_date
     */
    public function splitNewsDate($news_date)
    {
        return explode('-', $news_date);
    }

    /**
     * POSTされたランクの値を取得する
     * @param Integer $news_id
     */
    public function getPostRank($news_id)
    {
        if (strlen($news_id) > 0 && is_numeric($news_id) == true) {
            $key = 'pos-' . $news_id;
            $input_pos = $_POST[$key];

            return $input_pos;
        }
    }

    //画像関係の関数？
    public function lfInitFile(&$objUpFile)
    {
        $objUpFile->addFile(
            '一覧-メイン画像',
            'main_list_image',
            array('jpg', 'gif', 'png'),
            IMAGE_SIZE,
            false,
            SMALL_IMAGE_WIDTH,
            SMALL_IMAGE_HEIGHT
        );
//        $objUpFile->addFile('詳細-メイン画像', 'main_image', array('jpg', 'gif', 'png'), IMAGE_SIZE, false, NORMAL_IMAGE_WIDTH, NORMAL_IMAGE_HEIGHT);
//        $objUpFile->addFile('詳細-メイン拡大画像', 'main_large_image', array('jpg', 'gif', 'png'), IMAGE_SIZE, false, LARGE_IMAGE_WIDTH, LARGE_IMAGE_HEIGHT);
//        for ($cnt = 1; $cnt <= PRODUCTSUB_MAX; $cnt++) {
//            $objUpFile->addFile("詳細-サブ画像$cnt", "sub_image$cnt", array('jpg', 'gif', 'png'), IMAGE_SIZE, false, NORMAL_SUBIMAGE_WIDTH, NORMAL_SUBIMAGE_HEIGHT);
//            $objUpFile->addFile("詳細-サブ拡大画像$cnt", "sub_large_image$cnt", array('jpg', 'gif', 'png'), IMAGE_SIZE, false, LARGE_SUBIMAGE_WIDTH, LARGE_SUBIMAGE_HEIGHT);
//        }
    }

    public function lfInitDownFile(&$objDownFile)
    {
        $objDownFile->addFile('ダウンロード販売用ファイル', 'down_file', explode(',', DOWNLOAD_EXTENSION), DOWN_SIZE, true, 0, 0);
    }

//    public function lfGetSearchParam($arrPost)
//    {
//        $arrSearchParam = array();
//        $objFormParam = new SC_FormParam_Ex();
//
//        parent::lfInitParam($objFormParam);
//        $objFormParam->setParam($arrPost);
//        $arrSearchParam = $objFormParam->getSearchArray();
//
//        return $arrSearchParam;
//    }

    public function lfSetViewParam_InputPage(&$objUpFile, &$arrForm)
    {
//        // カテゴリマスターデータ取得
//        $objDb = new SC_Helper_DB_Ex();
//        list($this->arrCatVal, $this->arrCatOut) = $objDb->sfGetLevelCatList(false);
//
//        if (isset($arrForm['category_id']) && !is_array($arrForm['category_id'])) {
//            $arrForm['category_id'] = SC_Utils_Ex::jsonDecode($arrForm['category_id']);
//        }
//        $this->tpl_json_category_id = !empty($arrForm['category_id']) ? SC_Utils_Ex::jsonEncode($arrForm['category_id']) : SC_Utils_Ex::jsonEncode(array());
//        if ($arrForm['status'] == '') {
//            $arrForm['status'] = DEFAULT_PRODUCT_DISP;
//        }
//        if ($arrForm['product_type_id'] == '') {
//            $arrForm['product_type_id'] = DEFAULT_PRODUCT_DOWN;
//        }
//        if (OPTION_PRODUCT_TAX_RULE) {
//            // 編集の場合は設定された税率、新規の場合はデフォルトの税率を取得
//            if ($arrForm['product_id'] == '') {
//                $arrRet = SC_Helper_TaxRule_Ex::getTaxRule();
//            } else {
//                $arrRet = SC_Helper_TaxRule_Ex::getTaxRule($arrForm['product_id'], $arrForm['product_class_id']);
//            }
//            $arrForm['tax_rate'] = $arrRet['tax_rate'];
//        }
//        // アップロードファイル情報取得(Hidden用)
//        $arrHidden = ;
        $arrForm['arrHidden'] = $objUpFile->getHiddenFileList();

        // 画像ファイル表示用データ取得
        $arrForm['arrFile'] = $objUpFile->getFormFileList(IMAGE_TEMP_URLPATH, IMAGE_SAVE_URLPATH);

//        // ダウンロード商品実ファイル名取得
//        $arrForm['down_realfilename'] = $objDownFile->getFormDownFile();
//
//        // 基本情報(デフォルトポイントレート用)
//        $arrForm['arrInfo'] = SC_Helper_DB_Ex::sfGetBasisData();
//
//        // サブ情報ありなしフラグ
//        $arrForm['sub_find'] = $this->hasSubProductData($arrForm);

        return $arrForm;
    }

    public function hasSubProductData($arrSubProductData)
    {
        $has_subproduct_data = false;

        for ($i = 1; $i <= PRODUCTSUB_MAX; $i++) {
            if (SC_Utils_Ex::isBlank($arrSubProductData['sub_title' . $i]) == false
                || SC_Utils_Ex::isBlank($arrSubProductData['sub_comment' . $i]) == false
                || SC_Utils_Ex::isBlank($arrSubProductData['sub_image' . $i]) == false
                || SC_Utils_Ex::isBlank($arrSubProductData['sub_large_image' . $i]) == false
                || SC_Utils_Ex::isBlank($arrSubProductData['temp_sub_image' . $i]) == false
                || SC_Utils_Ex::isBlank($arrSubProductData['temp_sub_large_image' . $i]) == false
            ) {
                $has_subproduct_data = true;
                break;
            }
        }

        return $has_subproduct_data;
    }

    public function lfSetOnloadJavaScript_InputPage($anchor_hash = '')
    {
//        return "eccube.checkStockLimit('" . DISABLED_RGB . "');fnInitSelect('category_id_unselect'); fnMoveSelect('category_id_unselect', 'category_id');" . $anchor_hash;
    }

    public function lfGetRecommendProducts(&$arrForm)
    {
        $arrRecommend = array();

        for ($i = 1; $i <= RECOMMEND_PRODUCT_MAX; $i++) {
            $keyname = 'recommend_id' . $i;
            $delkey = 'recommend_delete' . $i;
            $commentkey = 'recommend_comment' . $i;

            if (!isset($arrForm[$delkey])) {
                $arrForm[$delkey] = null;
            }

            if ((isset($arrForm[$keyname]) && !empty($arrForm[$keyname])) && $arrForm[$delkey] != 1) {
                $objProduct = new SC_Product_Ex();
                $arrRecommend[$i] = $objProduct->getDetail($arrForm[$keyname]);
                $arrRecommend[$i]['product_id'] = $arrForm[$keyname];
                $arrRecommend[$i]['comment'] = $arrForm[$commentkey];
            }
        }

        return $arrRecommend;
    }

    public function lfInitFormParam_UploadImage(&$objFormParam)
    {
        $objFormParam->addParam('image_key', 'image_key', '', '', array());
    }

    public function lfInitFormParam_PreEdit(&$objFormParam, $arrPost)
    {
        $objFormParam->addParam('商品ID', 'product_id', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->setParam($arrPost);
        $objFormParam->convParam();
    }

    public function lfInitFormParam(&$objFormParam, $arrPost)
    {
//        $objFormParam->addParam('商品ID', 'product_id', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
//        $objFormParam->addParam('商品名', 'name', STEXT_LEN, 'KVa', array('EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
//        $objFormParam->addParam('商品カテゴリ', 'category_id', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
//        $objFormParam->addParam('公開・非公開', 'status', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
//        $objFormParam->addParam('商品ステータス', 'product_status', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));

        $objFormParam->addParam('id', 'id');
//        $objFormParam->addParam('日付(年)', 'year', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
//        $objFormParam->addParam('日付(月)', 'month', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
//        $objFormParam->addParam('日付(日)', 'day', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam(
            'バナータイトル',
            'banner_title',
            STEXT_LEN,
            'KVa',
            array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'SPTAB_CHECK')
        );
        $objFormParam->addParam('バナーURL', 'banner_url', URL_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam('バナーテキスト', 'banner_text', LTEXT_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam('別ウィンドウで開く', 'banner_select', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
//        $objFormParam->addParam('save_main_image', 'save_main_image', '', '', array());

//        if (!$arrPost['has_product_class']) {
//            // 新規登録, 規格なし商品の編集の場合
//            $objFormParam->addParam('商品種別', 'product_type_id', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
//            $objFormParam->addParam('ダウンロード商品ファイル名', 'down_filename', STEXT_LEN, 'KVa', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
//            $objFormParam->addParam('ダウンロード商品実ファイル名', 'down_realfilename', MTEXT_LEN, 'KVa', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
//            $objFormParam->addParam('temp_down_file', 'temp_down_file', '', '', array());
//            $objFormParam->addParam('save_down_file', 'save_down_file', '', '', array());
//            $objFormParam->addParam('商品コード', 'product_code', STEXT_LEN, 'KVa', array('EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
//            $objFormParam->addParam(NORMAL_PRICE_TITLE, 'price01', PRICE_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK', 'ZERO_START'));
//            $objFormParam->addParam(SALE_PRICE_TITLE, 'price02', PRICE_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK', 'ZERO_START'));
//            if (OPTION_PRODUCT_TAX_RULE) {
//                $objFormParam->addParam('消費税率', 'tax_rate', PERCENTAGE_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
//            }
//            $objFormParam->addParam('在庫数', 'stock', AMOUNT_LEN, 'n', array('SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK', 'ZERO_START'));
//            $objFormParam->addParam('在庫無制限', 'stock_unlimited', INT_LEN, 'n', array('SPTAB_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
//        }
//        $objFormParam->addParam('商品送料', 'deliv_fee', PRICE_LEN, 'n', array('NUM_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK', 'ZERO_START'));
//        $objFormParam->addParam('ポイント付与率', 'point_rate', PERCENTAGE_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK', 'ZERO_START'));
//        $objFormParam->addParam('発送日目安', 'deliv_date_id', INT_LEN, 'n', array('NUM_CHECK'));
//        $objFormParam->addParam('販売制限数', 'sale_limit', AMOUNT_LEN, 'n', array('SPTAB_CHECK', 'ZERO_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK', 'ZERO_START'));
//        $objFormParam->addParam('メーカー', 'maker_id', INT_LEN, 'n', array('NUM_CHECK'));
//        $objFormParam->addParam('メーカーURL', 'comment1', URL_LEN, 'a', array('SPTAB_CHECK', 'URL_CHECK', 'MAX_LENGTH_CHECK'));
//        $objFormParam->addParam('検索ワード', 'comment3', LLTEXT_LEN, 'KVa', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
//        $objFormParam->addParam('備考欄(SHOP専用)', 'note', LLTEXT_LEN, 'KVa', array('SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
//        $objFormParam->addParam('一覧-メインコメント', 'main_list_comment', MTEXT_LEN, 'KVa', array('EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
//        $objFormParam->addParam('詳細-メインコメント', 'main_comment', LLTEXT_LEN, 'KVa', array('EXIST_CHECK', 'SPTAB_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('save_main_list_image', 'save_main_list_image', '', '', array());
        $objFormParam->addParam('save_main_image', 'save_main_image', '', '', array());
        $objFormParam->addParam('save_main_large_image', 'save_main_large_image', '', '', array());
        $objFormParam->addParam('temp_main_list_image', 'temp_main_list_image', '', '', array());
        $objFormParam->addParam('temp_main_image', 'temp_main_image', '', '', array());
        $objFormParam->addParam('temp_main_large_image', 'temp_main_large_image', '', '', array());

//

        $objFormParam->setParam($arrPost);
        $objFormParam->convParam();
    }

    public function lfSetScaleImage(&$objUpFile, $image_key)
    {
        $subno = str_replace('sub_large_image', '', $image_key);
        switch ($image_key) {
            case 'main_large_image':
                // 詳細メイン画像
                $this->lfMakeScaleImage($objUpFile, $image_key, 'main_image');
            case 'main_image':
                // 一覧メイン画像
                $this->lfMakeScaleImage($objUpFile, $image_key, 'main_list_image');
                break;
            case 'sub_large_image' . $subno:
                // サブメイン画像
                $this->lfMakeScaleImage($objUpFile, $_POST['image_key'], 'sub_image' . $subno);
                break;
            default:
                break;
        }
    }

    public function getAnchorHash($anchor_key)
    {
        if ($anchor_key != '') {
            return "location.hash='#" . htmlspecialchars($anchor_key) . "'";
        } else {
            return '';
        }
    }

    public function lfCheckError_Edit(&$objFormParam, &$objUpFile, &$objDownFile, $arrForm)
    {
        $objErr = new SC_CheckError_Ex($arrForm);
        $arrErr = array();

        // 入力パラメーターチェック
        $arrErr = $objFormParam->checkError();

        // アップロードファイル必須チェック
        $arrErr = array_merge((array)$arrErr, (array)$objUpFile->checkExists());

        // HTMLタグ許可チェック
        $objErr->doFunc(array('詳細-メインコメント', 'main_comment', $this->arrAllowedTag), array('HTML_TAG_CHECK'));
        for ($cnt = 1; $cnt <= PRODUCTSUB_MAX; $cnt++) {
            $objErr->doFunc(
                array('詳細-サブコメント' . $cnt, 'sub_comment' . $cnt, $this->arrAllowedTag),
                array('HTML_TAG_CHECK')
            );
        }

        // 規格情報がない商品の場合のチェック
        if ($arrForm['has_product_class'] != true) {
            // 在庫必須チェック(在庫無制限ではない場合)
            if ($arrForm['stock_unlimited'] != UNLIMITED_FLG_UNLIMITED) {
                $objErr->doFunc(array('在庫数', 'stock'), array('EXIST_CHECK'));
            }
            // ダウンロード商品ファイル必須チェック(ダウンロード商品の場合)
            if ($arrForm['product_type_id'] == PRODUCT_TYPE_DOWNLOAD) {
                $arrErr = array_merge((array)$arrErr, (array)$objDownFile->checkExists());
                $objErr->doFunc(array('ダウンロード商品ファイル名', 'down_filename'), array('EXIST_CHECK'));
            }
        }

        $arrErr = array_merge((array)$arrErr, (array)$objErr->arrErr);

        return $arrErr;
    }

    public function lfGetFormParam_PreEdit(&$objUpFile, &$objDownFile, $product_id)
    {
        $arrForm = array();

        // DBから商品データ取得
        $arrForm = $this->lfGetProductData_FromDB($product_id);
        // DBデータから画像ファイル名の読込
        $objUpFile->setDBFileList($arrForm);
        // DBデータからダウンロードファイル名の読込
        $objDownFile->setDBDownFile($arrForm);

        return $arrForm;
    }

    public function lfGetProductData_FromDB($product_id)
    {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $arrProduct = array();

        // 商品データ取得
        $col = '*';
        $table = <<< __EOF__
            dtb_products AS T1
            LEFT JOIN (
                SELECT product_id AS product_id_sub,
                    product_code,
                    price01,
                    price02,
                    deliv_fee,
                    stock,
                    stock_unlimited,
                    sale_limit,
                    point_rate,
                    product_type_id,
                    down_filename,
                    down_realfilename
                FROM dtb_products_class
            ) AS T2
                ON T1.product_id = T2.product_id_sub
__EOF__;
        $where = 'product_id = ?';
        $objQuery->setLimit('1');
        $arrProduct = $objQuery->select($col, $table, $where, array($product_id));

        // カテゴリID取得
        $col = 'category_id';
        $table = 'dtb_product_categories';
        $where = 'product_id = ?';
        $objQuery->setOption('');
        $arrProduct[0]['category_id'] = $objQuery->getCol($col, $table, $where, array($product_id));

        // 規格情報ありなしフラグ取得
        $objDb = new SC_Helper_DB_Ex();
        $arrProduct[0]['has_product_class'] = $objDb->sfHasProductClass($product_id);

        // 規格が登録されていなければ規格ID取得
        if ($arrProduct[0]['has_product_class'] == false) {
            $arrProduct[0]['product_class_id'] = SC_Utils_Ex::sfGetProductClassId($product_id, '0', '0');
        }

        // 商品ステータス取得
        $objProduct = new SC_Product_Ex();
        $productStatus = $objProduct->getProductStatus(array($product_id));
        $arrProduct[0]['product_status'] = $productStatus[$product_id];

        // 関連商品データ取得
        $arrRecommend = $this->lfGetRecommendProductsData_FromDB($product_id);
        $arrProduct[0] = array_merge($arrProduct[0], $arrRecommend);

        return $arrProduct[0];
    }

    public function lfGetRecommendProductsData_FromDB($product_id)
    {
        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $arrRecommendProducts = array();

        $col = 'recommend_product_id,';
        $col .= 'comment';
        $table = 'dtb_recommend_products';
        $where = 'product_id = ?';
        $objQuery->setOrder('rank DESC');
        $arrRet = $objQuery->select($col, $table, $where, array($product_id));

        $no = 1;
        foreach ($arrRet as $arrVal) {
            $arrRecommendProducts['recommend_id' . $no] = $arrVal['recommend_product_id'];
            $arrRecommendProducts['recommend_comment' . $no] = $arrVal['comment'];
            $no++;
        }

        return $arrRecommendProducts;
    }

    public function lfDeleteTempFile(&$objUpFile, $image_key)
    {
        // TODO: SC_UploadFile::deleteFileの画像削除条件見直し要
        $arrTempFile = $objUpFile->temp_file;
        $arrKeyName = $objUpFile->keyname;

        foreach ($arrKeyName as $key => $keyname) {
            if ($keyname != $image_key) {
                continue;
            }

            if (!empty($arrTempFile[$key])) {
                $temp_file = $arrTempFile[$key];
                $arrTempFile[$key] = '';

                if (!in_array($temp_file, $arrTempFile)) {
                    $objUpFile->deleteFile($image_key);
                } else {
                    $objUpFile->temp_file[$key] = '';
                    $objUpFile->save_file[$key] = '';
                }
            } else {
                $objUpFile->temp_file[$key] = '';
                $objUpFile->save_file[$key] = '';
            }
        }
    }



    public function lfSaveUploadFiles(&$objUpFile, $product_id)
    {
        // TODO: SC_UploadFile::moveTempFileの画像削除条件見直し要
        $objImage = new SC_Image_Ex($objUpFile->temp_dir);
        $arrKeyName = $objUpFile->keyname;
        $arrTempFile = $objUpFile->temp_file;
        $arrSaveFile = $objUpFile->save_file;
        $arrImageKey = array();
        foreach ($arrTempFile as $key => $temp_file) {
            if ($temp_file) {
                $objImage->moveTempImage($temp_file, $objUpFile->save_dir);
                $arrImageKey[] = $arrKeyName[$key];
                if (!empty($arrSaveFile[$key])
                    && !$this->lfHasSameProductImage($product_id, $arrImageKey, $arrSaveFile[$key])
                    && !in_array($temp_file, $arrSaveFile)
                ) {
                    $objImage->deleteImage($arrSaveFile[$key], $objUpFile->save_dir);
                }
            }
        }
    }
}
