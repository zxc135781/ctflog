<?php

/* orz
-- enphp : https://git.oschina.net/mz/mzphp2
 */
// error_reporting(E_ALL ^ E_NOTICE);
error_reporting(E_ALL);

require_once 'sandbox.php';
$seed = time();
echo "\$seed = $seed\r\n";
srand($seed);
define('INS_OFFSET', rand(0x0, 0xffff));
$regs = array('eax' => 0x0, 'ebp' => 0x0, 'esp' => 0x0, 'eip' => 0x0);
function aslr(&$O00, $O0O) {
	$O00 = $O00 + 0x60000000 + INS_OFFSET + 0x1;
}
$func = get_defined_functions()["internal"];
$func_ = array_flip($func);
array_walk($func_, 'aslr');
$plt = array_flip($func_);
if (isset($_REQUEST['plt'])) {
	print_r($plt);
}
function handleData($OOO) {
	$O000 = strlen($OOO);
	$O00O = $O000 / 0x4 + 0x1 * ($O000 % 0x4);
	$O0O0 = str_split($OOO, 0x4);
	$O0O0[$O00O - 0x1] = str_pad($O0O0[$O00O - 0x1], 0x4, ' ');
	foreach ($O0O0 as $O0OO => &$OO00) {
		$OO00 = strrev(bin2hex($OO00));
	}
	return $O0O0;
}
function genCanary() {
	$OOOO = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQEST123456789';
	$O0000 = $OOOO[rand(0, strlen($OOOO) - 0x1)];
	$O000O = $OOOO[rand(0, strlen($OOOO) - 0x1)];
	$O00O0 = $OOOO[rand(0, strlen($OOOO) - 0x1)];
	$O00OO = ' ';
	return handleData($O0000 . $O000O . $O00O0 . $O00OO)[0];
}
$canary = genCanary();
$canarycheck = $canary;
function checkCanary() {
	global $canary;
	global $canarycheck;
	if ($canary != $canarycheck) {
		die('emmmmmm...Don\'t attack me!');
	}
}
class O0OO0 {
	private $ebp, $stack, $esp;
	public function __construct($O0OOO, $OO000) {
		$this->stack = array();
		global $regs;
		$this->ebp = &$regs['ebp'];
		$this->esp = &$regs['esp'];
		$this->ebp = 0xfffe0000 + rand(0x0, 0xffff);
		global $canary;
		$this->stack[$this->ebp - 0x4] = &$canary;
		$this->stack[$this->ebp] = $this->ebp + rand(0x0, 0xffff);
		$this->esp = $this->ebp - rand(0x20, 0x60) * 0x4;
		$this->stack[$this->ebp + 0x4] = dechex($O0OOO);
		if ($OO000 != NULL) {
			$this->pushdata($OO000);
		}
	}
	public function pushdata($OO0O0) {
		$OO0O0 = handleData($OO0O0);
		for ($OO0OO = 0; $OO0OO < count($OO0O0); $OO0OO++) {
			$this->stack[$this->esp + $OO0OO * 0x4] = $OO0O0[$OO0OO];
			//no args in my stack haha
			checkCanary();
		}
	}
	public function recoverData($OOO0O) {
		return hex2bin(strrev($OOO0O));
	}
	public function outputdata() {
		global $regs;
		echo 'root says: ';
		while (0x1) {
			if ($this->esp == $this->ebp - 0x4) {
				break;
			}
			$this->pop('eax');
			$OOOOO = $this->recoverData($regs['eax']);
			$O00000 = explode(' ', $OOOOO);
			echo $O00000[0];
			if (count($O00000) > 0x1) {
				break;
			}
		}
	}
	public function ret() {
		$this->esp = $this->ebp;
		$this->pop('ebp');
		$this->pop('eip');
		print_r($this->stack);
		$this->call();
	}
	public function getDataFromReg($O000OO) {
		global $regs;
		$O00O00 = $this->recoverData($regs[$O000OO]);
		$O00O0O = explode(' ', $O00O00);
		return $O00O0O[0];
	}
	public function call() {
		global $regs;
		global $plt;
		$O00OOO = hexdec($regs['eip']);
		if (isset($_REQUEST[$O00OOO])) {
			$this->pop('eax');
			$O0O000 = (int) $this->getDataFromReg('eax');
			$O0O00O = array();
			for ($O0O0O0 = 0; $O0O0O0 < $O0O000; $O0O0O0++) {
				$this->pop('eax');
				$O0O0OO = $this->getDataFromReg('eax');
				array_push($O0O00O, $_REQUEST[$O0O0OO]);
			}
			echo "call_user_func_array {$plt[$O00OOO]}\r\n";
			print_r($O0O00O);
			echo "\r\n";
			$r = call_user_func_array($plt[$O00OOO], $O0O00O);
			print_r($r);
		} else {
			echo "call_user_func {$plt[$O00OOO]}\r\n";
			call_user_func($plt[$O00OOO]);
		}
	}
	public function push($O0OO0O) {
		global $regs;
		$O0OOO0 = $regs[$O0OO0O];
		if (hex2bin(strrev($O0OOO0)) == NULL) {
			die('data error');
		}
		$this->stack[$this->esp] = $O0OOO0;
		$this->esp -= 0x4;
	}
	public function pop($OO0000) {
		global $regs;
		$regs[$OO0000] = $this->stack[$this->esp];
		$this->esp += 0x4;
	}
	public function __call($OO000O, $OO00O0) {
		checkCanary();
	}
}
class_alias('O0OO0', 'stack', 0);
print_R('O0OO0');
print_R('stack');
if (isset($_REQUEST['data'])) {
	$phpinfo_addr = array_search('phpinfo', $plt);
	$gets = $_REQUEST['data'];
	$main_stack = new stack($phpinfo_addr, $gets);
	echo '--------------------output---------------------</br></br>';
	$main_stack->outputdata();
	echo '</br></br>------------------phpinfo()------------------</br>';
	$main_stack->ret();
}
echo "\r\n\r\n";
// print_r(ini_get_all());
print_r(getcwd());
