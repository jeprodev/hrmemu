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
 * /
// prevent direct access from users
//defined('_JEXEC') or die('Restricted area');

header('content-type: text/css');
$menuId = htmlspecialchars($_GET['hrmenu_id'], ENT_QUOTES);
?>
.clr{ clear: both; }

div#<?php echo $menuId; ?>
div#<?php echo $menuId; ?>
div#<?php echo $menuId; ?>
div#<?php echo $menuId; ?>
div#<?php echo $menuId; ?>

div#<?php echo $menuId; ?>
div#<?php echo $menuId; ?>{} */