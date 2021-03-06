<?php
/*##################################################
 *                          DownloadFileController.class.php
 *                            -------------------
 *   begin                : August 24, 2014
 *   copyright            : (C) 2014 Julien BRISWALTER
 *   email                : julienseth78@phpboost.com
 *
 *
 ###################################################
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 ###################################################*/

 /**
 * @author Julien BRISWALTER <julienseth78@phpboost.com>
 */
 
class DownloadFileController extends AbstractController
{
	private $downloadfile;
	
	public function execute(HTTPRequestCustom $request)
	{
		$id = $request->get_getint('id', 0);
		
		if (!empty($id))
		{
			try {
				$this->downloadfile = DownloadService::get_downloadfile('WHERE download.id = :id', array('id' => $id));
			} catch (RowNotFoundException $e) {
				$error_controller = PHPBoostErrors::unexisting_page();
				DispatchManager::redirect($error_controller);
			}
		}
		
		if ($this->downloadfile !== null && !DownloadAuthorizationsService::check_authorizations($this->downloadfile->get_id_category())->read())
		{
			$error_controller = PHPBoostErrors::user_not_authorized();
			DispatchManager::redirect($error_controller);
		}
		else if ($this->downloadfile !== null && $this->downloadfile->is_visible())
		{
			$this->downloadfile->set_number_downloads($this->downloadfile->get_number_downloads() + 1);
			DownloadService::update_number_downloads($this->downloadfile);
			DownloadCache::invalidate();
			
			$status = 200;
			$file_headers = get_headers($this->downloadfile->get_url()->absolute(), true);
			if (is_array($file_headers))
			{
				if(preg_match('/^HTTP\/[12]\.[01] (\d\d\d)/', $file_headers[0], $matches))
					$status = (int)$matches[1];
			}
			
			if ($status == 200)
			{
				header('Content-Disposition: attachment; filename="' . urldecode(basename($this->downloadfile->get_url()->absolute())) . '"');
				header('Content-Description: File Transfer');
				header('Content-Transfer-Encoding: binary');
				header('Accept-Ranges: bytes');
				header('Content-Type: application/force-download');
				set_time_limit(0);
				readfile($this->downloadfile->get_url()->absolute());
			}
			else
			{
				$error_controller = new UserErrorController(LangLoader::get_message('error', 'status-messages-common'), LangLoader::get_message('download.message.error.file_not_found', 'common', 'download'), UserErrorController::WARNING);
				DispatchManager::redirect($error_controller);
			}
		}
		else
		{
			$error_controller = PHPBoostErrors::unexisting_page();
			DispatchManager::redirect($error_controller);
		}
	}
}
?>
