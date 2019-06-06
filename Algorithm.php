<?php
# 内功 算法 algorithm

$arr = [20,45,93,67,10,97,52,88,33,92];
// 冒泡排序
function bubbleSort(&$arr)
{
    $swapped = false;
    $bound = count($arr) - 1;
    for ($i = 0, $c = count($arr); $i < $c; $i++) {
        for ($j = 0; $j < $bound; $j++) {
            if ($arr[$j + 1] < $arr[$j]) {
                list($arr[$j], $arr[$j + 1]) = array($arr[$j + 1], $arr[$j]);
                $swapped = true;
                $newBound = $j;
            }
        }
        $bound = $newBound;
        if (!$swapped) break; //没有发生交换，算法结束
    }
}
bubbleSort($arr);
var_dump($arr);