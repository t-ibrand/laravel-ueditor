<?php namespace iBrand\UEditor;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use iBrand\UEditor\Uploader\UploadScrawl;
use iBrand\UEditor\Uploader\UploadFile;
use iBrand\UEditor\Uploader\UploadCatch;

class Controller extends BaseController
{
	public function server(Request $request)
	{
		$config = config('UEditorUpload.upload');

		$action = $request->get('action');
		switch ($action) {
			case 'config':
				$result = $config;
				break;
			case 'uploadimage':
				$upConfig = [
					"pathFormat" => $config['imagePathFormat'],
					"maxSize"    => $config['imageMaxSize'],
					"allowFiles" => $config['imageAllowFiles'],
					'fieldName'  => $config['imageFieldName'],
				];
				$result   = with(new UploadFile($upConfig, $request))->upload();
				break;
			case 'uploadscrawl':
				$upConfig = [
					"pathFormat" => $config['scrawlPathFormat'],
					"maxSize"    => $config['scrawlMaxSize'],
					//   "allowFiles" => $config['scrawlAllowFiles'],
					"oriName"    => "scrawl.png",
					'fieldName'  => $config['scrawlFieldName'],
				];
				$result   = with(new UploadScrawl($upConfig, $request))->upload();

				break;
			case 'uploadvideo':
				$upConfig = [
					"pathFormat" => $config['videoPathFormat'],
					"maxSize"    => $config['videoMaxSize'],
					"allowFiles" => $config['videoAllowFiles'],
					'fieldName'  => $config['videoFieldName'],
				];
				$result   = with(new UploadFile($upConfig, $request))->upload();

				break;
			case 'uploadfile':
			default:
				$upConfig = [
					"pathFormat" => $config['filePathFormat'],
					"maxSize"    => $config['fileMaxSize'],
					"allowFiles" => $config['fileAllowFiles'],
					'fieldName'  => $config['fileFieldName'],
				];
				$result   = with(new UploadFile($upConfig, $request))->upload();

				break;

			/* 列出图片 */
			case 'listimage':

				if (config('UEditorUpload.core.mode') == 'local') {
					$result = with(new Lists(
						$config['imageManagerAllowFiles'],
						$config['imageManagerListSize'],
						$config['imageManagerListPath'],
						$request))->getList();
				}

				break;
			/* 列出文件 */
			case 'listfile':
				if (config('UEditorUpload.core.mode') == 'local') {
					$result = with(new Lists(
						$config['fileManagerAllowFiles'],
						$config['fileManagerListSize'],
						$config['fileManagerListPath'],
						$request))->getList();
				}

				break;

			/* 抓取远程文件 */
			case 'catchimage':

				$upConfig = [
					"pathFormat" => $config['catcherPathFormat'],
					"maxSize"    => $config['catcherMaxSize'],
					"allowFiles" => $config['catcherAllowFiles'],
					"oriName"    => "remote.png",
					'fieldName'  => $config['catcherFieldName'],
				];

				$sources = \Input::get($upConfig['fieldName']);
				$list    = [];
				foreach ($sources as $imgUrl) {
					$upConfig['imgUrl'] = $imgUrl;
					$info               = with(new UploadCatch($upConfig, $request))->upload();

					array_push($list, [
						"state"    => $info["state"],
						"url"      => $info["url"],
						"size"     => $info["size"],
						"title"    => htmlspecialchars($info["title"]),
						"original" => htmlspecialchars($info["original"]),
						"source"   => htmlspecialchars($imgUrl),
					]);
				}
				$result = [
					'state' => count($list) ? 'SUCCESS' : 'ERROR',
					'list'  => $list,
				];

				break;
		}

		return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
	}
}