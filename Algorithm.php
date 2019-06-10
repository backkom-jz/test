<?php
# 内功 算法 algorithm
// https://segmentfault.com/a/1190000016325416
$arr = [20, 45, 93, 67, 10, 97, 52, 88, 33, 92];
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


// 选择排序
// 选择排序也类似于冒泡排序，它有两个for循环，从0到n。
// 冒泡排序和选择排序的区别在于，在最坏的情况下，选择排序使交换次数达到最大n - 1，而冒泡排序可以需要 n * n 次交换。
// 在选择排序中，最佳情况、最坏情况和平均情况具有相似的时间复杂度。
function selectionSort(array &$arr)
{
    $count = count($arr);
    for ($j = 0; $j <= $count - 1; $j++) {
        $min = $arr[$j];
        for ($i = $j + 1; $i < $count; $i++) {
            if ($arr[$i] < $min) {
                $min = $arr[$i];

                $minPos = $i;
            }
        }
        list($arr[$j], $arr[$minPos]) = [$min, $arr[$j]];
    }
}

// 插入排序
// 插入排序具有和冒泡排序相似的时间复杂度。区别在于交换的次数远低于冒泡排序
function insertionSort(array &$arr)
{
    $len = count($arr);
    for ($i = 1; $i < $len; $i++) {
        $key = $arr[$i];
        $j = $i - 1;
        while ($j >= 0 && $arr[$j] > $key) {
            // 交换
            $arr[$j + 1] = $arr[$j];
            $j--;
        }
        $arr[$j + 1] = $key;
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// 分治思想
/**
 * 归并排序
 * 核心：两个有序子序列的归并(function merge)
 * 时间复杂度任何情况下都是 O(nlogn)
 * 空间复杂度 O(n)
 * 发明人: 约翰·冯·诺伊曼
 * 速度仅次于快速排序，为稳定排序算法，一般用于对总体无序，但是各子项相对有序的数列
 * 一般不用于内(内存)排序，一般用于外排序
 */
function mergeSort(array &$arr)
{
    $len = count($arr);
    if ($len == 1) return $arr;
    $mid = (int)($len / 2);

    //把待排序数组分割成两半
    $left = mergeSort(array_slice($arr, 0, $mid));
    $right = mergeSort(array_slice($arr, $mid));

    return merge($left, $right);
}

function merge(array $left, array $right)
{
    //初始化两个指针
    $leftIndex = $rightIndex = 0;
    $leftLength = count($left);
    $rightLength = count($right);
    //临时空间
    $combine = [];

    //比较两个指针所在的元素
    while ($leftIndex < $leftLength && $rightIndex < $rightLength) {
        //如果左边的元素大于右边的元素，就将右边的元素放在单独的数组，并将右指针向后移动
        if ($left[$leftIndex] > $right[$rightIndex]) {
            $combine[] = $right[$rightIndex];
            $rightIndex++;
        } else {
            //如果右边的元素大于左边的元素，就将左边的元素放在单独的数组，并将左指针向后移动
            $combine[] = $left[$leftIndex];
            $leftIndex++;
        }
    }

    //右边的数组全部都放入到了返回的数组，然后把左边数组的值放入返回的数组
    while ($leftIndex < $leftLength) {
        $combine[] = $left[$leftIndex];
        $leftIndex++;
    }

    //左边的数组全部都放入到了返回的数组，然后把右边数组的值放入返回的数组
    while ($rightIndex < $rightLength) {
        $combine[] = $right[$rightIndex];
        $rightIndex++;
    }

    return $combine;
}

/**
 * 快速排序是找出一个元素（理论上可以随便找一个）作为基准(pivot),然后对数组进行分区操作,使基准左边元素的值都不大于基准值,基准右边的元素值 都不小于基准值，如此作为基准的元素调整到排序后的正确位置。递归快速排序，将其他n-1个元素也调整到排序后的正确位置。最后每个元素都是在排序后的正 确位置，排序完成。所以快速排序算法的核心算法是分区操作，即如何调整基准的位置以及调整返回基准的最终位置以便分治递归。
 * 时间复杂度 最坏O(n2) 平均 O(nlogn)
 * 空间复杂度 O(log2n)~O(n)
 */

function quickSort(&$arr)
{
    $count = count($arr);
    if ($count <= 1) {
        return $arr;
    }
    $left = $right = [];
    for ($i = 1; $i < $count; $i++) {
        if ($arr[$i] < $arr[0]) {
            $left[] = $arr[$i];
        } else {
            $right[] = $arr[$i];
        }
    }
    $left = quickSort($left);
    $right = quickSort($right);
    return array_merge($left, [$arr[0]], $right);
}

$start = microtime(true);
quickSort($arr, 0, count($arr) - 1);
$end = microtime(true);
$used = $end - $start;
echo "qSortV1 used $used s" . PHP_EOL;



/**
 * 桶排序
 * 不是一种基于比较的排序
 * T(N, M) = O(M + N) N是带排序的数据的个数，M是数据值的数量
 * 当 M >> N 时，需要考虑使用基数排序
 */

function bucketSort(array &$data)
{
    $bucketLen = max($data) - min($data) +1;
    $bucket = array_fill(0,$bucketLen,[]);

    for ($i = 0; $i< count($data);$i++){
        array_push($bucket[$data[$i]-min($data)],$data[$i]);
    }

    $k = 0;
    for($i =0;$i <$bucketLen;$i++){
        $currentBucketLen = count($bucket[$i]);
        for($j = 0;$j < $currentBucketLen;$j++){
            $data[$k]=$bucket[$i][$j];
            $k++;
        }
    }
}