<?php
/**
 *    @package      modules
 *    @subpackage   mod_hrmenu
 *    @link         http://jeprodev.net
 *    @copyright (C) 2009 - 2011
 *    @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
// prevent direct access from users
defined('_JEXEC') or die('Restricted area');

jimport('joomla.filesystem.file');
jimport('joomla.plugin.helper');

require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'helper.php');

$document = JFactory::getDocument();
$app = JFactory::getApplication();
$menu = $app->getMenu();
$active = $menu->getActive();
$activeId = isset($active) ? $active->id : $menu->getDefault()->id;
$path = isset($active) ? $active->tree : array();
//retrieve menu id if set
if($params->get("hrmenu_id", '') === ''){ $params->set('hrmenu_id', 'hr_menu_' . $module->id); }
/** getting configuration **/
$menuId = $params->get('hrmenu_id', 'hrmenu');
$menuWidth = $params->get('hrmenu_sub_menu_width', 160);
$menuHeight = $params->get('hrmenu_height', 35);
$hrMenuLayout = $params->get('hrmenu_template', 'horizontal');
$menuTransition = $params->get('hrmenu_transition', 'bounce');
$menuEase = $params->get('hrmenu_ease', 'easeOut');
$behavior = $params->get('hrmenu_command_type', 'hrmenu');
$menuFxDuration = $params->get('hrmenu_duration', 500);
$menuTestOverFlow = $params->get('hrmenu_test_overflow', 0);
$menuOpenType = $params->get('hrmenu_open_type', 'slide');
$menuTimeIn = $params->get('hrmenu_time_in', 20);
$menuTimeOut = $params->get('hrmenu_time_out', 400);
$isMobile = '0';
$logoImage = $params->get('hrmenu_logo_image', '');
$logoLink = $params->get('hrmenu_logo_link', '');
$logoHeight = $params->get('hrmenu_logo_height', '');
$logoWidth = $params->get('hrmenu_logo_width', '');

/** get setting for login params **/
$menu_show_login_pad = $params->get('hrmenu_show_login_pad', 1);
$menu_show_login_pad_width = $params->get('hrmenu_show_login_pad_width', 120);

/** get fancy parameters  */
$menuUseFancy = $params->get('hrmenu_use_fancy', 1);
$fancyTransition = $params->get('hrmenu_fancy_transition', 'linear');
$fancyEase = $params->get('hrmenu_fancy_easy', 'easeOut');
$fancyDuration = $params->get('hrmenu_fancy_duration', 400);
/** get items */
$hrMenuItems = modHrMenuHelper::getItems($params);

// if no item in the menu then exit
if (!count($hrMenuItems) OR !$hrMenuItems)
    return false;

$languageDirection = $document->getDirection();
// page title management
if ($active) {
    $pageTitle = $document->getTitle();
    $title = $pageTitle;
    if (preg_match("/||/", $active->title)) {
        $title = explode("||", $active->title);
        $title = str_replace($active->title, $title[0], $pageTitle);
    }
    if (preg_match("/\[/", $active->title)) {
        if (!$title){ $title = $active->title; }
        $title = explode("[", $title);
        $title = str_replace($active->title, $title[0], $pageTitle);
    }
    $document->setTitle($title);
}

/**------ detection for mobiles ----**/
if (isset($_SERVER['HTTP_USER_AGENT']) && (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPad') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPod') || strstr($_SERVER['HTTP_USER_AGENT'], 'Android'))) {
    $behavior = 'click';
    $isMobile = '1';
}

/** add style sheet */
$hrMenuTheme = $params->get('hrmenu_theme', 'jeprodev');
$hrMenuUseCss = $params->get('hrmenu_use_css', 1);
if($hrMenuLayout == 'vertical'){

}else{
    if($languageDirection == 'rtl' && JFile::exists(dirname(__FILE__) . 'assets/themes/' . $hrMenuTheme . '/css/hrmenu_rtl.css')){
        $document->addStyleSheet(JURI::base(true) . '/modules/mod_hrmenu/assets/themes/' . $hrMenuTheme . '/css/hrmenu_rtl.css');
    }else{
        $document->addStyleSheet(JURI::base(true) . '/modules/mod_hrmenu/assets/themes/' . $hrMenuTheme . '/css/hrmenu.css');
    }
    if($hrMenuUseCss == 1){
        if($languageDirection == ''){
            //$document->addStyleSheet(JURI::base(true) . '/modules/mod_hrmenu/assets/themes/' . $hrMenuTheme . '/css/hrmenu_rtl.css.php?hrmenu_id=' . $menuId);
        }else{
            $document->addStyleSheet(JURI::base(true) . '/modules/mod_hrmenu/assets/themes/' . $hrMenuTheme . '/css/hrmenu.css');
            //$document->addStyleSheet(JURI::base(true) . '/modules/mod_hrmenu/assets/themes/' . $hrMenuTheme . '/css/hrmenu.css.php?hrmenu_id=' . $menuId);
        }
    }
}

if (JFile::exists('modules/mod_hrmenu/assets/themes/' . $hrMenuTheme . '/css/ie7.css')) {
    echo '
		<!--[if lte IE 7]>
		<link href="' . JURI::base(true) . '/modules/mod_hrmenu/assets/themes/' . $hrMenuTheme . '/css/ie7.css" rel="stylesheet" type="text/css" />
		<![endif]-->';
}
/*$document->addStyleSheet('modules/mod_hrmenu/assets/css/hrmenu.css'); */
//modHrMenuHelper::setStyleScript($menuId, $params);

/** add javascript */
JHtml::_('jquery.framework');

$document->addScript(JURI::base(). 'modules/mod_hrmenu/assets/js/hrmenu.js');
$script = "jQuery(document).ready(function(){";
$script .= "jQuery('#" . $menuId . "').DropDownHrMenu({";
$script .= "fx_transition: '" . $menuTransition . "', ";
$script .= "fx_duration : " . (int) $menuFxDuration . ", ";
$script .= "menuId: '" . $menuId . "', ";
$script .= "test_overflow: '" . (int)$menuTestOverFlow . "', ";
$script .= "behavior : '" . $behavior . "', ";
$script .= "open_type: '" . $menuOpenType . "', ";
$script .= "time_in:" . (int)$menuTimeIn .", ";
$script .= "time_out:" . (int)$menuTimeOut . ", ";
$script .= "is_mobile:". $isMobile . ", item_width: " . $menuWidth . ", item_height: " . $menuHeight . ",";
$script .= "show_active_sub_items : '1' ";
$script .= "}); });";

$document->addScriptDeclaration($script);

/** adding fancy effect */
//if( $menuUseFancy == 1){
$document->addScript(JURI::base() . '/modules/mod_hrmenu/assets/js/hrmenu_fancy.js');
$js = "jQuery(document).ready(function(){";
$js .= " jQuery('#" . $menuId . "').HrMenuFancySlider({";
$js .= " fancyTransition : '" . $fancyTransition . "', ";
$js .= " fancyDuration : " . (int) $fancyDuration . "}); });";

$document->addScriptDeclaration($js);
//}

require JModuleHelper::getLayoutPath('mod_hrmenu', $params->get('hrmenu_template', 'default'));