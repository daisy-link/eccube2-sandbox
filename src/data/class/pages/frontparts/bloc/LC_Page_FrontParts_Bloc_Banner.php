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

define('CALENDAR_ROOT', DATA_REALDIR.'module/Calendar'.DIRECTORY_SEPARATOR);
require_once CLASS_EX_REALDIR . 'page_extends/frontparts/bloc/LC_Page_FrontParts_Bloc_Ex.php';

/**
 * Calendar のページクラス.
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 * @version $ $
 */
class LC_Page_FrontParts_Bloc_Banner extends LC_Page_FrontParts_Bloc_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
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

        // バナー情報取得
        $this->banner = $this->lfGetBanner();
    }

    /**
     * カレンダー情報取得.
     *
     * @param  integer $disp_month 表示する月数
     * @return array   カレンダー情報の配列を返す
     */



    public function lfGetBanner()
    {
        $banner = new SC_Query;
        $this->banner = $banner->select('*', 'dtb_banners');
        if(!is_array($this->banner)){
            $banner = $this->banner[0];
            return $banner;
        }else{
            $banner = 'バナーの情報が登録されていません。';
            return $banner;
        }

    }
}


