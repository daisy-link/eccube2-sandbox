<?php
require_once '../require.php';
require_once CLASS_EX_REALDIR . 'page_extends/LC_Page_Ex.php';

/**
 * ユーザーカスタマイズ用のページクラス
 *
 * 管理画面から自動生成される
 *
 * @package Page
 */
class LC_Page_User extends LC_Page_Ex
{
    /** テンプレートクラス名1 */
    public $tpl_class_name1 = array();

    /** テンプレートクラス名2 */
    public $tpl_class_name2 = array();

    /** JavaScript テンプレート */
    public $tpl_javascript;

    public $orderby;

    public $mode;

    /** 検索条件(内部データ) */
    public $arrSearchData = array();

    /** 検索条件(表示用) */
    public $arrSearch = array();

    public $tpl_subtitle = '';

    /** ランダム文字列 **/
    public $tpl_rnd = '';
    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init()
    {
        parent::init();

        $masterData                 = new SC_DB_MasterData_Ex();
        $this->arrSTATUS            = $masterData->getMasterData('mtb_status');
        $this->arrSTATUS_IMAGE      = $masterData->getMasterData('mtb_status_image');
        $this->arrDELIVERYDATE      = $masterData->getMasterData('mtb_delivery_date');
        $this->arrPRODUCTLISTMAX    = $masterData->getMasterData('mtb_product_list_max');
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process()
    {
        parent::process();
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    function action()
    {
        //決済処理中ステータスのロールバック
        $objPurchase = new SC_Helper_Purchase_Ex();
        $objPurchase->cancelPendingOrder(PENDING_ORDER_CANCEL_FLAG);

        $objProduct = new SC_Product_Ex();
        // パラメーター管理クラス
        $objFormParam = new SC_FormParam_Ex();

        // パラメーター情報の初期化
        $this->lfInitParam($objFormParam);

        // 値の設定
        $objFormParam->setParam($_REQUEST);

        // 入力値の変換
        $objFormParam->convParam();

        // 値の取得
        $this->arrForm = $objFormParam->getHashArray();

        //modeの取得
        $this->mode = $this->getMode();

        //表示条件の取得
        $this->arrSearchData = array(
            'category_id'   => $this->lfGetCategoryId(intval($this->arrForm['category_id'])),
            'maker_id'      => intval($this->arrForm['maker_id']),
            'name'          => $this->arrForm['name']
        );
        $this->orderby = $this->arrForm['orderby'];

        //ページング設定
        $this->tpl_pageno   = $this->arrForm['pageno'];
        $this->disp_number  = $this->lfGetDisplayNum($this->arrForm['disp_number']);

        // 画面に表示するサブタイトルの設定
        $this->tpl_subtitle = $this->lfGetPageTitle($this->mode, $this->arrSearchData['category_id']);

        // 画面に表示する検索条件を設定
        $this->arrSearch    = $this->lfGetSearchConditionDisp($this->arrSearchData);

        // 商品一覧データの取得
        $arrSearchCondition = $this->lfGetSearchCondition($this->arrSearchData);
        $this->tpl_linemax  = $this->lfGetProductAllNum($arrSearchCondition);
        $urlParam           = "category_id={$this->arrSearchData['category_id']}&pageno=#page#";
        // モバイルの場合に検索条件をURLの引数に追加
        if (SC_Display_Ex::detectDevice() === DEVICE_TYPE_MOBILE) {
            $searchNameUrl = urlencode(mb_convert_encoding($this->arrSearchData['name'], 'SJIS-win', 'UTF-8'));
            $urlParam .= "&mode={$this->mode}&name={$searchNameUrl}&orderby={$this->orderby}";
        }
        $this->objNavi      = new SC_PageNavi_Ex($this->tpl_pageno, $this->tpl_linemax, $this->disp_number, 'eccube.movePage', NAVI_PMAX, $urlParam, SC_Display_Ex::detectDevice() !== DEVICE_TYPE_MOBILE);
        $this->arrProducts  = $this->lfGetProductsList($arrSearchCondition, $this->disp_number, $this->objNavi->start_row, $objProduct);

        switch ($this->getMode()) {
            case 'json':
                $this->doJson($objProduct);
                break;

            default:
                $this->doDefault($objProduct, $objFormParam);
                break;
        }

        $this->tpl_rnd = SC_Utils_Ex::sfGetRandomString(3);
    }

    /**
     * パラメーター情報の初期化
     *
     * @param  SC_FormParam_Ex $objFormParam フォームパラメータークラス
     * @return void
     */
    public function lfInitParam(&$objFormParam)
    {
        // 抽出条件
        // XXX カートインしていない場合、チェックしていない
        $objFormParam->addParam('カテゴリID', 'category_id', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('メーカーID', 'maker_id', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('商品名', 'name', STEXT_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam('表示順序', 'orderby', STEXT_LEN, 'KVa', array('MAX_LENGTH_CHECK'));
        $objFormParam->addParam('ページ番号', 'pageno', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('表示件数', 'disp_number', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
        // カートイン
        $objFormParam->addParam('規格1', 'classcategory_id1', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('規格2', 'classcategory_id2', INT_LEN, 'n', array('NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('数量', 'quantity', INT_LEN, 'n', array('EXIST_CHECK', 'ZERO_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('商品ID', 'product_id', INT_LEN, 'n', array('ZERO_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('商品規格ID', 'product_class_id', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK', 'MAX_LENGTH_CHECK'));
    }

    /**
     * カテゴリIDの取得
     *
     * @param int $category_id
     * @return integer|void カテゴリID
     */
    public function lfGetCategoryId($category_id)
    {
        // 指定なしの場合、0 を返す
        if (empty($category_id)) return 0;

        // 正当性チェック
        $objCategory = new SC_Helper_Category_Ex();
        if ($objCategory->isValidCategoryId($category_id)) {
            return $category_id;
        } else {
            SC_Utils_Ex::sfDispSiteError(CATEGORY_NOT_FOUND);
        }
    }

    /**
     * パラメーターの読み込み
     *
     * @return void
     */
    public function lfGetDisplayNum($display_number)
    {
        // 表示件数
        return (SC_Utils_Ex::sfIsInt($display_number))
            ? $display_number
            : current(array_keys($this->arrPRODUCTLISTMAX));
    }

    public function lfGetPageTitle($mode, $category_id = 0)
    {
        if ($mode == 'search') {
            return '検索結果';
        } elseif ($category_id == 0) {
            return '全商品';
        } else {
            $objCategory = new SC_Helper_Category_Ex();
            $arrCat = $objCategory->get($category_id);

            return $arrCat['category_name'];
        }
    }

    public function lfGetSearchCondition($arrSearchData)
    {
        $searchCondition = array(
            'where'             => '',
            'arrval'            => array(),
            'where_category'    => '',
            'arrvalCategory'    => array()
        );

        // カテゴリからのWHERE文字列取得
        if ($arrSearchData['category_id'] != 0) {
            list($searchCondition['where_category'], $searchCondition['arrvalCategory']) = SC_Helper_DB_Ex::sfGetCatWhere($arrSearchData['category_id']);
        }
        // ▼対象商品IDの抽出
        // 商品検索条件の作成（未削除、表示）
        $searchCondition['where'] = SC_Product_Ex::getProductDispConditions('alldtl');

        if (strlen($searchCondition['where_category']) >= 1) {
            $searchCondition['where'] .= ' AND EXISTS (SELECT * FROM dtb_product_categories WHERE ' . $searchCondition['where_category'] . ' AND product_id = alldtl.product_id)';
            $searchCondition['arrval'] = array_merge($searchCondition['arrval'], $searchCondition['arrvalCategory']);
        }

        // 商品名をwhere文に
        $name = $arrSearchData['name'];
        $name = str_replace(',', '', $name);
        // 全角スペースを半角スペースに変換
        $name = str_replace('　', ' ', $name);
        // スペースでキーワードを分割
        $names = preg_split('/ +/', $name);
        // 分割したキーワードを一つずつwhere文に追加
        foreach ($names as $val) {
            if (strlen($val) > 0) {
                $searchCondition['where']    .= ' AND ( alldtl.name ILIKE ? OR alldtl.comment3 ILIKE ?) ';
                $searchCondition['arrval'][]  = "%$val%";
                $searchCondition['arrval'][]  = "%$val%";
            }
        }

        // メーカーらのWHERE文字列取得
        if ($arrSearchData['maker_id']) {
            $searchCondition['where']   .= ' AND alldtl.maker_id = ? ';
            $searchCondition['arrval'][] = $arrSearchData['maker_id'];
        }

        // 在庫無し商品の非表示
        if (NOSTOCK_HIDDEN) {
            $searchCondition['where'] .= ' AND EXISTS(SELECT * FROM dtb_products_class WHERE product_id = alldtl.product_id AND del_flg = 0 AND (stock >= 1 OR stock_unlimited = 1))';
        }

        // XXX 一時期内容が異なっていたことがあるので別要素にも格納している。
        $searchCondition['where_for_count'] = $searchCondition['where'];

        return $searchCondition;
    }

    public function lfGetProductAllNum($searchCondition)
    {
        // 検索結果対象となる商品の数を取得
        $objQuery   =& SC_Query_Ex::getSingletonInstance();
        $objQuery->setWhere($searchCondition['where_for_count']);
        $objProduct = new SC_Product_Ex();

        return $objProduct->findProductCount($objQuery, $searchCondition['arrval']);
    }

    public function lfGetSearchConditionDisp($arrSearchData)
    {
        $objQuery   =& SC_Query_Ex::getSingletonInstance();
        $arrSearch  = array('category' => '指定なし', 'maker' => '指定なし', 'name' => '指定なし');
        // カテゴリ検索条件
        if ($arrSearchData['category_id'] > 0) {
            $arrSearch['category']  = $objQuery->get('category_name', 'dtb_category', 'category_id = ?', array($arrSearchData['category_id']));
        }

        // メーカー検索条件
        if (strlen($arrSearchData['maker_id']) > 0) {
            $objMaker = new SC_Helper_Maker_Ex();
            $maker = $objMaker->getMaker($arrSearchData['maker_id']);
            $arrSearch['maker']     = $maker['name'];
        }

        // 商品名検索条件
        if (strlen($arrSearchData['name']) > 0) {
            $arrSearch['name']      = $arrSearchData['name'];
        }

        return $arrSearch;
    }

    public function lfGetProductsList($searchCondition, $disp_number, $startno, &$objProduct)
    {
        $objQuery =& SC_Query_Ex::getSingletonInstance();

        $arrOrderVal = array();

        // 表示順序
        switch ($this->orderby) {
            // 販売価格が安い順
            case 'price':
                $p = $objProduct->setProductsOrder('price02', 'dtb_products_class', 'ASC');
                return $p;
                break;

            // 新着順
            case 'date':
                $p = $objProduct->setProductsOrder('create_date', 'dtb_products', 'DESC');
                return $p;
                break;

//            case 'all_selling':
//                return $objProduct->setProductsSellingOrder();
//                braek;

            default:
                if (strlen($searchCondition['where_category']) >= 1) {
                    $dtb_product_categories = '(SELECT *, COUNT() FROM dtb_product_categories WHERE '.$searchCondition['where_category'].')';
                    $arrOrderVal           = $searchCondition['arrvalCategory'];
                } else {
                    $dtb_product_categories = 'dtb_product_categories';
                }
                $col = 'MAX(T3.rank * 2147483648 + T2.rank)';
                $from = "$dtb_product_categories T2 JOIN dtb_category T3 ON T2.category_id = T3.category_id";
                $where = 'T2.product_id = alldtl.product_id';
                $sub_sql = $objQuery->getSql($col, $from, $where);

                $objQuery->setOrder("($sub_sql) DESC ,product_id DESC");
                break;
        }
        // 取得範囲の指定(開始行番号、行数のセット)
        $objQuery->setLimitOffset($disp_number, $startno);
        $objQuery->setWhere($searchCondition['where']);

        // 表示すべきIDとそのIDの並び順を一気に取得
        $arrProductId = $objProduct->findProductIdsOrder($objQuery, array_merge($searchCondition['arrval'], $arrOrderVal));

        $objQuery =& SC_Query_Ex::getSingletonInstance();
        $arrProducts = $objProduct->getListByProductIds($objQuery, $arrProductId);

        // 規格を設定
        $objProduct->setProductsClassByProductIds($arrProductId);
        $arrProducts['productStatus'] = $objProduct->getProductStatus($arrProductId);

        return $arrProducts;
    }

    public function doDefault(&$objProduct, &$objFormParam)
    {
        //商品一覧の表示処理
        $strnavi            = $this->objNavi->strnavi;
        // 表示文字列
        $this->tpl_strnavi  = empty($strnavi) ? '&nbsp;' : $strnavi;

        // 規格1クラス名
        $this->tpl_class_name1  = $objProduct->className1;

        // 規格2クラス名
        $this->tpl_class_name2  = $objProduct->className2;

        // 規格1
        $this->arrClassCat1     = $objProduct->classCats1;

        // 規格1が設定されている
        $this->tpl_classcat_find1 = $objProduct->classCat1_find;
        // 規格2が設定されている
        $this->tpl_classcat_find2 = $objProduct->classCat2_find;

        $this->tpl_stock_find       = $objProduct->stock_find;
        $this->tpl_product_class_id = $objProduct->product_class_id;
        $this->tpl_product_type     = $objProduct->product_type;

        // 商品ステータスを取得
        $this->productStatus = $this->arrProducts['productStatus'];
        unset($this->arrProducts['productStatus']);
        $this->tpl_javascript .= 'eccube.productsClassCategories = ' . SC_Utils_Ex::jsonEncode($objProduct->classCategories) . ';';
        if (SC_Display_Ex::detectDevice() === DEVICE_TYPE_PC) {
            //onloadスクリプトを設定. 在庫ありの商品のみ出力する
            foreach ($this->arrProducts as $arrProduct) {
                if ($arrProduct['stock_unlimited_max'] || $arrProduct['stock_max'] > 0) {
                    $js_fnOnLoad .= "fnSetClassCategories(document.product_form{$arrProduct['product_id']});";
                }
            }
        }

        //カート処理
        $target_product_id = intval($this->arrForm['product_id']);
        if ($target_product_id > 0) {
            // 商品IDの正当性チェック
            if (!SC_Utils_Ex::sfIsInt($this->arrForm['product_id'])
                || !SC_Helper_DB_Ex::sfIsRecord('dtb_products', 'product_id', $this->arrForm['product_id'], 'del_flg = 0 AND status = 1')) {
                SC_Utils_Ex::sfDispSiteError(PRODUCT_NOT_FOUND);
            }

            // 入力内容のチェック
            $arrErr = $this->lfCheckError($objFormParam);
            if (empty($arrErr)) {
                $this->lfAddCart($this->arrForm);

                // 開いているカテゴリーツリーを維持するためのパラメーター
                $arrQueryString = array(
                    'category_id' => $this->arrForm['category_id'],
                );

                SC_Response_Ex::sendRedirect(CART_URL, $arrQueryString);
                SC_Response_Ex::actionExit();
            }
            $js_fnOnLoad .= $this->lfSetSelectedData($this->arrProducts, $this->arrForm, $arrErr, $target_product_id);
        } else {
            // カート「戻るボタン」用に保持
            $netURL = new Net_URL();
            //該当メソッドが無いため、$_SESSIONに直接セット
            $_SESSION['cart_referer_url'] = $netURL->getURL();
        }

        $this->tpl_javascript   .= 'function fnOnLoad() {' . $js_fnOnLoad . '}';
        $this->tpl_onload       .= 'fnOnLoad(); ';
    }

    public function lfCheckError($objFormParam)
    {
        // 入力データを渡す。
        $arrForm =  $objFormParam->getHashArray();
        $objErr = new SC_CheckError_Ex($arrForm);
        $objErr->arrErr = $objFormParam->checkError();

        // 動的チェック
        if ($this->tpl_classcat_find1[$arrForm['product_id']]) {
            $objErr->doFunc(array('規格1', 'classcategory_id1'), array('EXIST_CHECK'));
        }
        if ($this->tpl_classcat_find2[$arrForm['product_id']]) {
            $objErr->doFunc(array('規格2', 'classcategory_id2'), array('EXIST_CHECK'));
        }

        return $objErr->arrErr;
    }

    public function lfAddCart($arrForm)
    {
        $objCartSess = new SC_CartSession_Ex();

        $product_class_id = $arrForm['product_class_id'];
        $objCartSess->addProduct($product_class_id, $arrForm['quantity']);
    }
}

$objPage = new LC_Page_User();
$objPage->init();
$objPage->process();
