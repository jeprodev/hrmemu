<?php
/****
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
if ($params->get('hrmenu_command_type', 'click_to_close') == 'click_to_close') {
    $close = '<div class="hrmenu_close">' . JText::_('MOD_HRMENU_CLOSE') . '</div>';
} else {
    $close = '';
}
$hrMenuFixedClass = ($params->get('hrmenu_position', 0) == 'bottom_fixed') ? ' hrmenu_fixed' : '';
$direction = $languageDirection == 'rtl' ? 'right' : 'left';
$start = (int)$params->get('hrmenu_start_level');
$document = JFactory::getDocument();

?>
<div id="hrmenu" class="<?php echo $hrMenuLayout; ?>" >
    <div class="hrmenu_wrapper <?php echo $languageDirection; ?>" id="<?php echo $menuId; ?>" >
        <div id="hrmenu_logo" class="hrmenu_logo" >
            <a href="#" id="hrmenu_mobile">
                <div id="hrmenu_text_link" ><?php echo JText::_('MOD_HRMENU_MENU_LABEL'); ?></div>&nbsp;
                <div id="hrmenu_icon_link" >
                    <img src="<?php echo JURI::base() . 'modules/mod_hrmenu/assets/images/list-4.png'; ?>" />
                </div>
            </a>
        </div>
        <div class="hrmenu_wrapper_menu"  >
            <ul class="nav_hrmenu <?php if($params->get('module_class_sfx')){ echo $params->get('module_class_sfx', ''); } ?>" > <!-- first level item wrapper -->
                <?php
                $zIndex = 35000;
                if(count($hrMenuItems)){
                    foreach($hrMenuItems as $item){ //print_r($item);
                        $item->mobile_data = isset($item->mobile_data) ? $item->mobile_data : '';
                        $itemLevel = ($start > 1) ? $item->level - $start + 1 :  $item->level;
                        if($params->get('hrmenu_called_from_level')){
                            $itemLevel = $itemLevel + $params->get('hrmenu_called_from_level') - 1;
                        }
                        $stopDropDown = $params->get('hrmenu_stop_drop_down_level', '0');
                        $stopDropDownClass = ($stopDropDown != '0' && $item->level >= $stopDropDown) ? ' no_drop_down' : '';

                        $createNewRow = (isset($item->createnewrow) AND $item->createnewrow) ? '<div style="clear:both; " ></div><div>' : '';
                        $columnStyles = isset($item->columnwidth) ? ' style="width:' . modHrMenuHelper::checkUnit($item->columnwidth) . '; float:left; " ' : '';
                        $nextColumnStyles = isset($item->nextcolumnwidth) ? 'style="width:' . modHrMenuHelper::checkUnit($item->nextcolumnwidth) . '; float:left;" ' : '';

                        if(isset($item->column) AND (isset($previous) AND !$previous->deeper )){
                            echo '</ul><div class="clr" ></div></div>' . $createNewRow . '<div class="hrmenu_2" ' . $columnStyles . '><ul class="hrmenu_2" >';
                        }
                        if(isset($item->content) AND $item->content){
                            echo '<li data-level="' . $itemLevel . '" class="hrmenu hrmenu_module ' . $stopDropDownClass . $item->class . ' level' . $itemLevel . ' ' . $item->li_class . '" ' . $item->mobile_data . '>' . $item->content;
                            $item->ftitle;
                        }

                        if($item->ftitle != ""){
                            $title = $item->anchor_title ? ' title ="' . $item->anchor_title . '"' : '';
                            $description = $item->desc ? '<div class="hrmenu_desc" >' . $item->desc . '</div>' : '';
                            // manage Html description
                            $classColumnTitle = $item->params->get('hrmenu_class_column_title', '') ? ' class="' . $item->params->get('hrmenu_class_column_title', '') . '"' : '';

                            $openTag = (isset($item->tagcoltitle) AND $item->tagcoltitle != 'none') ? '<' . $item->tagcoltitle . $classColumnTitle . '>' : '';
                            $closeTag = (isset($item->tagcoltitle) AND $item->tagcoltitle != 'none') ? '</' . $item->tagcoltitle . '>' : '' ;

                            //Manage image
                            if($item->menu_image){
                                //manage image roll over
                                $menuImageSplit = explode('.', $item->menu_image);
                                $imageRollOver = '';
                                if(isset($menuImageSplit[1])){
                                    //Manage active image
                                    if(isset($item->active) AND $item->active){
                                        $menuImageActive = $menuImageSplit[0] . $params-get('hrmenu_image_active_prefix', '_active') . '.' . $menuImageSplit[1];
                                        if(JFile::exists(JPATH_SITE . '/' .$menuImageActive)){
                                            $item->menu_image = $menuImageActive;
                                        }
                                    }

                                    //mange hover image
                                    $menuImageOver = $menuImageSplit[0] . $params->get('hrmenu_image_roll_prefix', '_hover') . '.' ;
                                    if(isset($item->active) AND $item->active AND JFile::exists(JPATH_ROOT . '/' . $menuImageSplit[0] . $params->get('hrmenu_image_active_prefix', '_active') . $params->get('hrmenu_image_roll_prefix', '_hover') . '.' . $menuImageSplit[1])){
                                        $imageRollOver = ' onmouseover="javascript:this.src=\'' . JURI::base(true) . '/' . $menuImageSplit[0] . $params->get('hrmenu_image_active_prefix', '_active') . $params->get('hrmenu_image_roll_prefix', '_hover') . '.' . $menuImageSplit[1] . '\'" onmouseout="javascript:this.src=\'' . JURI::base(true) . '/' . $item->menu_image . '\'"';
                                    }elseif(JFile::exists(JPATH_ROOT . '/' . $menuImageOver)){
                                        $imageRollOver = ' onmouseover="javascript:this.src=\'' . JURI::base(true) . '/' . $menuImageOver . '\'" onmouseout="javascript:this.src=\'' . JURI::base(true) . '/' . $item->menu_image . '\'"';
                                    }
                                }

                                $imageAlign = ($item->params->get('hrmenu_images_alignment', 'module_default') != 'module_default') ? $item->params->get('hrmenu_images_alignment', 'top') : $params->get('hrmenu_images_alignment', 'top');
                                $imageDimensions = ($item->params->get('hrmenu_params_image_width', '') != '' && ($item->get('hrmenu_params_image_height', '') != '')) ? ' width="' . $item->params->get('hrmenu_params_image_width', '') . '" height="' . $item->params->get('hrmenu_params_image_height', '') . '"' : '';

                                if($item->params->get('menu_text', 1) AND !$params->get('imageonly', '0')){
                                    switch($imageAlign){
                                        case 'bottom':
                                            $linkType = '<div class="hrmenu_title" >' . $item->ftitle . $description . '</div><img src="' . $item->menu_image . '" alt="' . $item->ftitle . '" style="display:block; margin:0 auto; " ' . $imageRollOver . $imageDimensions . ' />';
                                            break;
                                        case 'top' :
                                            $linkType = '<img src="' . $item->menu_title . '" alt="' . $item->ftitle . '" style="display:block; ';
                                            break;
                                        case 'right_bottom' :
                                            $linkType = '<div class="hrmenu_title" >' . $item->ftitle . $description . '</div><img src="' . $item->menu_image . '" alt="' . $item->ftitle . '" align="bottom" ' . $imageRollOver . $imageDimensions . '/>';
                                            break;
                                        case 'right_middle' :
                                            $linkType = '<div class="hrmenu_title" >' . $item->ftitle . $description . '</div><img src="' . $item->menu_image . '" alt="' . $item->ftitle . '" align="middle" ' . $imageRollOver . $imageDimensions . '/>';
                                            break;
                                        case 'right_top' :
                                            $linkType = '<div class="hrmenu_title" >' . $item->ftitle . $description . '</div><img src="' . $item->menu_image . '" alt="' . $item->ftitle . '" align="top" ' . $imageRollOver . $imageDimensions . '/>';;
                                            break;
                                        case 'left_bottom' :
                                            $linkType = '<img src="' . $item->menu_image . '" alt="' . $item->ftitle . '" align="bottom" ' . $imageRollOver . $imageDimensions . ' /><div class="hrmenu_title" >' . $item->ftitle . $description . '</div>';
                                            break;
                                        case 'left_middle' :
                                            $linkType = '<img src="' . $item->menu_image . '" alt="' . $item->ftitle . '" align="middle" ' . $imageRollOver . $imageDimensions . ' /><div class="hrmenu_title" >' . $item->ftitle . $description . '</div>';
                                            break;
                                        case 'left_top' :
                                            $linkType = '<img src="' . $item->menu_image . '" alt="' . $item->ftitle . '" align="top" ' . $imageRollOver . $imageDimensions . ' /><div class="hrmenu_title" >' . $item->ftitle . $description . '</div>';
                                            break;
                                        case 'default' :
                                        default:
                                            $linkType = '<img src="' . $item->menu_image . '" alt="' . $item->ftitle . '" align="left" ' . $imageRollOver . $imageDimensions . '/><div class="hrmenu_title" >' . $item->ftitle . $description . '</div>';
                                            break;
                                    }
                                }else{
                                    $linkType = '<img src="' . $item->menu_image . '" alt="' . $item->ftitle . '" ' . $imageRollOver . $imageDimensions . ' />';
                                }
                            }else{
                                $linkType = '<div class="hrmenu_title" >' . $item->ftitle . $description . '</div>';
                            }

                            echo '<li data-level="' . $itemLevel . '" class="hrmenu' . $stopDropDownClass . $item->class . ' level' . $itemLevel . ' ' . $item->liclass . '" style="z-index: ' . $zIndex . '; " ' . $item->mobile_data . '>';
                            switch($item->type){
                                case 'separator' :
                                    echo $openTag . '<div class="separator ' . $item->anchor_css . '">' . $linkType . '</div>' . $closeTag;
                                    break;
                                case 'heading' :
                                    echo $openTag . '<div class="nav_header ' . $item->anchor_css . '">' . $linkType . '</div>' . $closeTag;
                                    break;
                                case 'url' :
                                case 'component' :
                                    switch($item->browserNav){
                                        case 1:
                                            // _blank
                                            echo $openTag . '<a class="hrmenu ' . $item->anchor_css . '" href="' . $item->flink . '" ' . $title . $item->rel . '>' . $linkType . '</a>' . $closeTag;
                                            break;
                                        case 2 :
                                            //window.open
                                            echo $openTag . '<a class="hrmenu ' . $item->anchor_css . '" href="' . $item->flink . '" target="_blank" ' . $title .$item->rel . '>' . $linkType . '</a>' . $closeTag;
                                            break;
                                        case 0 :
                                        default :
                                            //onclick="window.open(this.href, \'targetWindow\',  \'toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes\'); return false;"
                                            echo $openTag . '<a class="hrmenu ' . $item->anchor_css . '" href="' . $item->flink . '" ' . $title . $item->rel . '>' . $linkType . '</a>' . $closeTag;
                                            break;
                                    }
                                    break;
                                case 'default' :
                                default :
                                    echo $openTag . '<a class="hrmenu ' . $item->anchor_css . '" href="' . $item->flink . '"' . $title . $item->rel . '>' . $linkType . '</a>' . $closeTag;
                                    break;
                            }

                        }

                        if($item->deeper){
                            if(isset($item->submenus_width) || isset($item->left_margin) || isset($item->column_background_color) || isset($item->submenu_container_height)){
                                $item->styles = ' style="';
                                $item->innerstyles = 'style="width:auto; ';
                                if($item->left_margin){
                                    $item->styles .= 'margin-' . $direction . ':' . modHrMenuHelper::checkUnit($item->left_margin) . '"; ';
                                }
                                if($item->top_margin){
                                    $item->styles .= 'margin-top:' . modHrMenuHelper::checkUnit($item->top_margin) . '; ';
                                }
                                if(isset($item->submenus_width)){
                                    $item->styles .= 'width:' . modHrMenuHelper::checkUnit($item->submenus_width) . '; ';
                                }
                                if($item->column_background_color){
                                    $item->styles .= 'background:' . $item->column_background_color . '; ';
                                }
                                if(isset($item->submenu_container_height) && $item->submenu_container_height){
                                    $item->innerstyles .= 'height:' . modHrMenuHelper::checkUnit($item->submenu_container_height) . '; ';
                                }
                                $item->styles .= '"';
                                $item->innerstyles .= '"';
                            }else{
                                $item->styles = '';
                                $item->innerstyles = '';
                            }
                            echo '<div class="hrmenu_float" ' . $item->styles . ' >' . $close . '<div class="hrmenu_drop_top" ><div class="hrmenu_drop_top_2" ></div></div><div class="hrmenu_drop_main" ' . $item->innerstyles . '><div class="hrmenu_drop_main_2" ><div class="hrmenu_2 first" ' . $nextColumnStyles . '><ul class="hrmenu_2" >';
                        }elseif($item->shallower){
                            // The next item is shallower.
                            echo '</li>' . str_repeat('</ul><div class="clr" ></div></div><div class="clr" </div></div></div><div class="hrmenu_drop_bottom" ><div class="hrmenu_drop_bottom_2" ></div></div></div></li>', $item->level_diff);
                        }elseif($item->is_end){
                            // the item is the last.
                            echo str_repeat('</li></ul><div class="clr" ></div></div><div class="clr" ></div></div></div><div class="hrmenu_drop_bottom" ><div class="hrmenu_drop_bottom_2" ></div></div></div>', $item->level_diff) . '</li>';
                        }else{
                            // The next item is on the same level.
                            echo '</li>';
                        }
                        $zIndex--;
                        $previous = $item;
                    }
                }
                ?>
            </ul>
        </div>
    </div>
</div>