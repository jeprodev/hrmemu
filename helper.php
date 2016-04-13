<?php
/*
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

class modHrMenuHelper
{
    public static function getItems($params){
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $menu = $app->getMenu();

        /** if no active menu, use default  */
        $active = ($menu->getActive()) ? $menu->getActive() : $menu->getDefault();
        $levels = $user->getAuthorisedViewLevels();

        asort($levels);
        $key = 'menu_items' . $params . implode(',', $levels) . '.' . $active->id;
        $cache = JFactory::getCache('mod_hrmenu', '');
        if(!($items = $cache->get($key))){
            /** initialise variables  */
            //$list = array();
            $modules = array();
            //$db = JFactory::getDBO();
            $document = JFactory::getDocument();

            /** load libraries */
            jimport('joomla.application.module.helper');

            $path = isset($active) ? $active->tree : array();
            $start = (int)$params->get('hrmenu_start_level', 1);
            $end = (int)$params->get('hrmenu_end_level', 6);
            $items = $menu->getItems('menutype', $params->get('hrmenu_type', 'mainmenu'));

            /** return if no items */
            if(!$items){ return false; }

            $lastItem = 0;

            /** list all modules */
            $modulesList = modHrMenuHelper::createModuleList();

            foreach($items as $i => $item){
                $isDependant = $params->get('hrmenu_dependant_items', 0) ? ($start > 1 && !in_array($item->tree[$start - 2], $path))   : false;
                if(($start && ($start > $item->level)) || ($end && ($item->level > $end)) || $isDependant){
                    unset($items[$i]);
                    continue;
                }

                $item->deeper = false;
                $item->shallower = false;
                $item->level_diff = 0;

                if(isset($items[$lastItem])){
                    $items[$lastItem]->deeper = ($item->level > $items[$lastItem]->level);
                    $items[$lastItem]->shallower = ($item->level < $items[$lastItem]->level);
                    $items[$lastItem]->level_diff = ($items[$lastItem]->level - $item->level);
                }

                /** Test if this is the last item */
                $item->is_end = !isset($items[$i + 1]);

                $item->parent = (boolean) $menu->getItems('parent_id', (int)$item->id, true);
                $item->active = false;
                $item->flink = $item->link;

                switch($item->type){
                    case 'separator':
                        continue;
                    case 'url':
                        if((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid') == faslse)){
                            $item->flink = $item->link . '&Itemid=' . $item->id;
                        }
                        $item->flink = JFilterOutput::ampReplace(htmlspecialchars($item->flink));
                        break;
                    case 'alias':
                        $item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
                        break;
                    default:
                        $router = JSite::getRouter();
                        if ($router->getMode() == JROUTER_MODE_SEF) {
                            $item->flink = 'index.php?Itemid=' . $item->id;
                        } else {
                            $item->flink .= '&Itemid=' . $item->id;
                        }
                        break;
                }

                if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false)) {
                    $item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
                } else {
                    $item->flink = JRoute::_($item->flink);
                }

                $item->anchor_css = htmlspecialchars($item->params->get('menu-anchor_css', ''));
                $item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''));
                $item->menu_image = $item->params->get('menu_image', '') ? htmlspecialchars($item->params->get('menu_image', '')) : '';

                $item->ftitle = htmlspecialchars($item->title);
                $item->ftitle = JFilterOutput::ampReplace($item->ftitle);
                $parentItem = modHrMenuHelper::getParentItem($item->parent_id, $items);
                $item->class = '';
                //$item->class = '_item' . $item->id;

                if (isset($active) && $active->id == $item->id) {
                    $item->class .= ' current';
                }

                /** add active class */
                if(is_array($path) && (($item->type == 'alias' && in_array($item->params->get('aliasoptions'), $path)) || in_array($item->id, $path))){
                    $item->class .= ' active';
                    $item->active = true;
                }

                // add the parent class
                if ($item->deeper) {
                    $item->class .= ' deeper';
                }

                if ($item->parent) {
                    if ($params->get('hrmenu_layout', 'horizontal') != '_:flatlist'){
                        $item->class .= ' parent';
                    }
                }

                // add last and first class
                $item->class .= $item->is_end ? ' last' : '';
                $item->class .= ! isset($items[$i - 1]) ? ' first' : '';

                if (isset($items[$lastItem])) {
                    $items[$lastItem]->class .= $items[$lastItem]->shallower ? ' last' : '';
                    $item->class .= $items[$lastItem]->deeper ? ' first' : '';
                    if (isset($items[$i + 1]) AND $item->level - $items[$i + 1]->level > 1) {
                        $parentItem->class .= ' last';
                    }
                }

                /** manage column */
                $item->colwidth = $item->params->get('column_width', '160');
                $item->createnewrow = $item->params->get('create_new_row', 0);

                $subWidthValues = NULL;
                preg_match('/\[subwidth=([0-9]+)\]/', $item->ftitle, $subWidthValues);
                $subWidth = isset($subWidthValues[1]) ? $subWidthValues[1] : '';
                if($subWidth){
                    $item->ftitle = preg_replace('/\[subwidth=[0-9]+\]/', '', $item->ftitle);
                }
                $item->submenucontainerwidth = $item->params->get('submenucontainerwidth', '') + $subWidth;
                $result = NULL;
                if($item->params->get('createcolumn', 0)){
                    //TODO: clean if test work
                    $item->column = true;
                    if(isset($parentItem->submenuswidth)) {
                        $parentItem->submenuswidth = strval($parentItem->submenuswidth) + strval($item->colwidth);
                    } else {
                        $parentItem->submenuswidth = strval($item->colwidth);
                    }

                    if(isset($items[$lastItem]) && $items[$lastItem]->deeper){
                        $items[$lastItem]->nextcolumnwidth = $item->colwidth;
                    }
                }elseif(preg_match('/\[col=([0-9]+)\]/', $item->ftitle, $result)){
                    $item->ftitle = str_replace('[newrow]', '', $item->ftitle);
                    $item->ftitle = preg_replace('/\[col=[0-9]+)\]/', '', $item->ftitle);
                    $item->column = true;

                    if(isset($parentItem->submenuswidth)){
                        $parentItem->submenuswidth = strval($parentItem->submenuswith) + strval($result[1]);
                    } else {
                        $parentItem->submenuswidth = strval($result[1]);
                    }

                    if(isset($items[$lastItem]) && $items[$lastItem]->deeper){
                        $items[$lastItem]->nextcolumnwidth = $result[1];
                    }
                    $item->colwidth = $result[1];
                }

                if(isset( $parentItem->submenucontainerwidth) && $parentItem->submenucontainerwidth){
                    $parentItem->submenuswidth = $parentItem->submenucontainerwidth;
                }
                /** manage module */
                $moduleId = $item->params->get('hrmenu_module', '');
                $style = $item->params->get('force_module_title', 0) ? 'xhtml' : '';

                if($item->params->get('insert_module', 0)){
                    if(!isset($modules[$moduleId])){
                        $modules[$moduleId] = modHrMenuHelper::genModuleById($moduleId, $modulesList, $style);
                    }
                    $item->content = '<div class="hrmenu_mod">' . $modules[$moduleId] . '<div class="clr"></div></div>';
                } elseif( preg_match('/\[modid=([0-9]+)\]/', $item->ftitle, $result)){
                    $item->ftitle = preg_match('/\[modid=[0-9]+\]/', '', $item->ftitle);
                    $item->content= '<div class="hrmenu_mod"' . modHrMenuHelper::genModuleById($result[1], $modulesList, $style) . '<div class="clr"></div></div>';
                }

                /** manage rel attribute */
                $item->rel = '';
                $rel = $item->params->get('hrmenu_relattr', '');
                if($rel){
                    $item->rel = ' rel="' . $rel .'"';
                } elseif( preg_match('/\[rel=[a-z]+\]/i', '', $result)) {
                    $item->ftitle = preg_replace('/\[rel=[a-z]+\]/i', '', $item->ftitle);
                    $item->rel = ' rel="'. $result[1] . '"';
                }

                /** manage link description */
                $item->description = $item->params->get('hrmenu_des', '');
                if($item->description){
                    $item->desc = $item->description;
                } else {
                    $result = explode("||", $item->ftitle);
                    if(isset($result[1])){
                        $item->desc = $result[1];
                    } else {
                        $item->desc = '';
                    }
                    $item->ftitle = $result[0];
                }

                /* add styles to the page for customization  */
                $menuID = $params->get('hrmenu_id', 'hrmenu');
                $itemStyles = "";
                $item->titleColor = $item->params->get('hrmenu_title_color', '');
                if($item->titleColor){
                    $itemStyles .= "div#" . $menuID . " ul.nav_hrmenu li.item" . $item->id . " > a div.hrmenu_title{ color:" . $item->titlecolor . " !important; } div#" . $menuID . " ul.nav_hrmenu li.item" . $item->id . " > span.separator div.hrmenu_title {color:" . $item->titlecolor . " !important; }";
                }
                $item->desccolor = $item->params->get('hrmenu_desccolor', '');
                if($item->desccolor){
                    $itemStyles .= "div#" . $menuID . " ul.nav_hrmenu li.item" . $item->id . " > a span.hrmenu_desc{color:" . $item->desccolor . " !important; }  div#" . $menuID . " ul.nav_hrmenu li.item" . $item->id . " > span.separator span.hrmenu_desc {color:" . $item->desccolor . " !important; }";
                }
                if($itemStyles){
                    $document->addStyleDeclaration($itemStyles);
                }
                // get plugin parameters that are used directly in the layout
                $item->leftmargin = $item->params->get('hrmenu_left_margin', '');
                $item->topmargin = $item->params->get('hrmenu_top_margin', '');
                $item->liclass = $item->params->get('hrmenu_li_class', '');
                $item->colbgcolor = $item->params->get('hrmenu_col_bg_color', '');
                $item->tagcoltitle = $item->params->get('hrmenu_tag_col_title', 'none');
                $item->submenucontainerheight = $item->params->get('hrmenu_sub_menu_container_height', '');

                $lastItem = $i;
            }
            if (isset($items[$lastItem])) {
                $items[$lastItem]->deeper = (($start ? $start : 1) > $items[$lastItem]->level);
                $items[$lastItem]->shallower = (($start ? $start : 1) < $items[$lastItem]->level);
                $items[$lastItem]->level_diff = ($items[$lastItem]->level - ($start ? $start : 1));
            }

            $cache->store($items, $key) ;
        }
        return $items;
    }

    static function genModuleById($moduleId, $modulesList, $style){
        $attributes['style'] = $style;
        $moduleTitle = $modulesList[$moduleId]->title;
        $moduleName = $modulesList[$moduleId]->module;

        if (JModuleHelper::isEnabled($moduleName)) {
            $module = JModuleHelper::getModule($moduleName, $moduleTitle);
            return JModuleHelper::renderModule($module, $attributes);
        }
        return 'Module ID=' . $moduleId . ' not found !';
    }


    static function getParentItem($id, $items){
        foreach($items as $item) {
            if($item->id == $id ){ return $item; }
        }
    }

    static function createModuleList(){
        $db = JFactory::getDBO();
        $query = " SELECT * FROM ". $db->quoteName('#__modules') . " WHERE published = 1 ORDER BY id;";

        $db->setQuery($query);
        $modules = $db->loadObjectList('id');
        return $modules;
    }



    static function parseImagePath($imgPath, $sub_dir='' ){
        $temp = str_replace(DIRECTORY_SEPARATOR, '/', $imgPath);
        $tempArr = explode('/', $temp);
        $imgFile = JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_hrmenu'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
        if($sub_dir != ""){
            $imgFile .= $sub_dir . DIRECTORY_SEPARATOR;
        }

        $imgFile .= $tempArr[count($tempArr) - 1];
        if(file_exists($imgFile)){
            $imagePath = 'modules/mod_hrmenu/assets/images/';
            if($sub_dir != ""){
                $imagePath .= $sub_dir . '/';
            }
            $imagePath .= $tempArr[count($tempArr) - 1];
            return $imagePath;
        }else{

        }
    }
}