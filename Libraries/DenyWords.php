<?php

class DenyWords 
{
	/**
	 * 验证文字是否通过敏感词验证
	 *
	 * 函数的详细描述(对功能的进一步解释，可以多行; 如无必要可省略)
	 *
	 * @param   string   要测试的文本字符串
	 * @param   App_Db   数据库实例,应指向数据库"ledu"
	 * @param   interger 项目号id(当前minigame为1, passport为2, 公有库为0)
	 * @return  integer  1为通过验证; 0为不通过
	 * @author  tomsui
	 * @version $Id: DenyWords.php v1.0 2010-12-8 23:12:54 tomsui $
	 */
	public function test($text, $db, $proj_id = 0)
	{
		if (trim($text) == '') {// 如果是空字符串直接退出
			return 1;
		}

		// 读取数据库
		$sql = "select word from dn_words where state = 1 and proj_id in (0, $proj_id)";
		$words = $db->fetchAll($sql);
		foreach ($words as $item) {
			$word = trim($item['word']);
			$regx .= $word . '|';
		}
		$regx = trim($regx, '|');
		$regx = self::preg_regex_to_pattern($regx, 'i');

		// 进行正则验证
		if (preg_match($regx, $text)) {
			$ret = 0;
		} else {
			$ret = 1;
		}

		return $ret;
	}


	/**
	 * 验证文字是否通过敏感词验证
	 *
	 * 函数的详细描述(对功能的进一步解释，可以多行; 如无必要可省略)
	 *
	 * @param   string   要替换的文本字符串
	 * @param   App_Db   数据库实例,应指向数据库"ledu"
	 * @param   interger 项目号id(当前minigame为1, passport为2, 公有库为0)
	 * @param   App_Db   被替换的目标字符串
	 * @return  string   替换后的字符串
	 * @author  tomsui
	 * @version $Id: DenyWords.php v1.0 2010-12-8 23:12:54 tomsui $
	 */
	public function replace($text, $db, $proj_id)
	{
		if (trim($text) == '') {// 如果是空字符串直接退出
			return $text;
		}

		// 读取数据库
		$sql = "select word from dn_words where state = 1 and proj_id in (0, $proj_id)";
		$words = $db->fetchAll($sql);
		foreach ($words as $item) {
			$word = trim($item['word']);
			$regx .= $word . '|';
		}
		$regx = trim($regx, '|');
		$regx = self::preg_regex_to_pattern($regx, 'i');

		// 进行正则替换
		function replace($matches) {
			return str_repeat('*', mb_strlen($matches[0], 'utf-8'));
		}
		return preg_replace_callback($regx, 'replace', $text);
	}
              
	protected function preg_regex_to_pattern($raw_regex, $modifiers = "")
	{
		if (! preg_match('{\\\\(?:/;$)}', $raw_regex)) {
			$cooked = preg_replace('!/!', '\/', $raw_regex);
		} else {
			$pattern = '{ [^\\\\/]+ |\\\\. |( / |\\\\$ ) }sx';
			$f = create_function('$matches', '
				if (empty($matches[1])) {
					return $matches[0];
				} else {
					return "\\\\" . $matches[1];
				}'
			);
			$cooked = preg_replace_callback($pattern, $f, $raw_regex);
		}
		return "/$cooked/$modifiers";
	}

}
