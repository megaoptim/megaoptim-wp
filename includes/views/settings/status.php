<?php
/********************************************************************
 * Copyright (C) 2017 Darko Gjorgjijoski (http://darkog.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 **********************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access is not allowed.' );
}
$debug = new MGO_Debug();
$report = $debug->generate_report();
?>
<div class="megaoptim-postbox">
    <form class="content-wrapper" method="POST" id="megaoptim-report-export">
        <div class="megaoptim-middle-content">
            <table id="megaoptim-report-table" class="megaoptim-table wp-list-table widefat fixed striped media">
                <tbody>
				<?php foreach ( $report as $key => $val ): ?>
                    <tr>
                        <th><?php echo $key; ?></th>
                        <td><?php echo $val; ?></td>
                    </tr>
				<?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="megaoptim-form-actions">
            <div class="options-save">
                <button class="megaoptim-export-table button-primary button-large" type="submit">
                    <?php _e('Export', 'megaoptim'); ?>
                </button>
            </div>
        </div>
    </form>
</div>
