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

require_once CLASS_EX_REALDIR . 'page_extends/LC_Page_Ex.php';

/**
 * Mypage の基底クラス.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $Id$
 */
class LC_Page_AbstractMypage extends LC_Page_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        // mypage 共通
        $this->tpl_title        = 'MYページ';
        $this->tpl_navi         = 'mypage/navi.tpl';
        $this->tpl_mainno       = 'mypage';
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        parent::process();
        // ログインチェック
        $objCustomer = new SC_Customer_Ex();

        // ログインしていない場合は必ずログインページを表示する
        if ($objCustomer->isLoginSuccess(true) === false) {
            // クッキー管理クラス
            $objCookie = new SC_Cookie_Ex();
            // クッキー判定(メールアドレスをクッキーに保存しているか）
            $this->tpl_login_email = $objCookie->getCookie('login_email');
            if ($this->tpl_login_email != '') {
                $this->tpl_login_memory = '1';
            }

            // POSTされてきたIDがある場合は優先する。
            if (isset($_POST['login_email'])
                && $_POST['login_email'] != ''
            ) {
                $this->tpl_login_email = $_POST['login_email'];
            }

            // 携帯端末IDが一致する会員が存在するかどうかをチェックする。
            if (SC_Display_Ex::detectDevice() === DEVICE_TYPE_MOBILE) {
                $this->tpl_valid_phone_id = $objCustomer->checkMobilePhoneId();
            }
            $this->tpl_title        = 'MYページ(ログイン)';
            $this->tpl_mainpage     = 'mypage/login.tpl';
        } else {
            //マイページ会員情報表示用共通処理
            $this->tpl_login     = true;
            $this->CustomerName1 = $objCustomer->getValue('name01');
            $this->CustomerName2 = $objCustomer->getValue('name02');
            $this->CustomerPoint = $objCustomer->getValue('point');
            $zip1 = new SC_Query();
            $col = 'zip01';
            $table = 'dtb_customer';
            $where = 'customer_id = ?';
            $customer_id = $_SESSION['customer']['customer_id'];

            $zip1 = $zip1->get($col, $table, $where, [$customer_id]);
            $this->CustomerZip1 = $zip1;
//            foreach($zip1 as $zip => $z){
//                foreach($z as $za => $zb){
//                    $this->CustomerZip1 = $zb;
//                }
//            }
            $zip2 = new SC_Query();
            $zip2 = $zip2->getOne('SELECT zip02 FROM dtb_customer WHERE customer_id = ?;', [$customer_id]);
            print_r($zip2);
            $this->CustomerZip2 = $zip2;

            $this->action();
        }


        $this->sendResponse();
    }
}
