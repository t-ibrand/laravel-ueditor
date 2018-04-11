<?php namespace iBrand\UEditor;

class Lists
{
	public function __construct($allowFiles, $listSize, $path, $request)
	{
		$this->allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);
		$this->listSize   = $listSize;
		$this->path       = $path;
		$this->request    = $request;
	}

	public function getList()
	{

		$size  = $this->request->get('size', $this->listSize);
		$start = $this->request->get('start', 0);
		$end   = $start + $size;
		/* 获取文件列表 */
		$path  = storage_path('app/public') . '/' . ltrim($this->path, '/');
		$files = $this->getfiles($path, $this->allowFiles);
		if (!count($files)) {
			return [
				"state" => "no match file",
				"list"  => [],
				"start" => $start,
				"total" => count($files),
			];
		}

		/* 获取指定范围的列表 */
		$len = count($files);
		for ($i = min($end, $len) - 1, $list = []; $i < $len && $i >= 0 && $i >= $start; $i--) {
			$list[] = $files[$i];
		}

		/* 返回数据 */
		$result = [
			"state" => "SUCCESS",
			"list"  => $list,
			"start" => $start,
			"total" => count($files),
		];

		return $result;
	}

	/**
	 * 遍历获取目录下的指定类型的文件
	 *
	 * @param       $path
	 * @param array $files
	 *
	 * @return array
	 */
	protected function getfiles($path, $allowFiles, &$files = [])
	{
		if (!is_dir($path)) {
			return null;
		}

		if (substr($path, strlen($path) - 1) != '/') {
			$path .= '/';
		}

		$handle = opendir($path);
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..') {
				$path2 = $path . $file;
				if (is_dir($path2)) {
					$this->getfiles($path2, $allowFiles, $files);
				} else {
					if (preg_match("/\.(" . $allowFiles . ")$/i", $file)) {

						$files[] = [
							'url'   => env('APP_URL') . str_replace(storage_path('app/public'), '', $path2),
							'mtime' => filemtime($path2),
						];
					}
				}
			}
		}

		return $files;
	}
}