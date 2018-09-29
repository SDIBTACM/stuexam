<?php

function splitpage($table, $searchsql = "") {
    $page = I('get.page', 1, 'intval');
    $each_page = C('EACH_PAGE');
    $pagenum = C('PAGE_NUM');
    $total = M($table)->where($searchsql)->count();
    $totalpage = ceil($total / $each_page);
    if ($totalpage == 0) $totalpage = 1;
    $page = $page < 1 ? 1 : $page;
    $page = $page > $totalpage ? $totalpage : $page;

    $offset = ($page - 1) * $each_page;
    $sqladd = "$offset,$each_page";

    $lastpage = $totalpage;
    $prepage = $page - 1;
    $nextpage = $page + 1;

    $startpage = $page - 4;
    $startpage = $startpage < 1 ? 1 : $startpage;
    $endpage = $startpage + $pagenum - 1;
    $endpage = $endpage > $totalpage ? $totalpage : $endpage;
    return array('page' => $page,
        'prepage' => $prepage,
        'startpage' => $startpage,
        'endpage' => $endpage,
        'nextpage' => $nextpage,
        'lastpage' => $lastpage,
        'eachpage' => $each_page,
        'sqladd' => $sqladd
    );
}

function showpagelast($pageinfo, $url, $urladd = "") {
    foreach ($pageinfo as $key => $value) {
        ${$key} = $value;
    }
    echo "<nav>";
    echo "<ul class='pagination'>";
    echo "<li><a href='{$url}?page=1&{$urladd}'>First</a></li>";
    if ($page == 1) {
        echo "<li class='disabled'><a href='javascript:;'>Previous</a></li>";
    } else {
        echo "<li><a href='{$url}?page=$prepage&{$urladd}'>Previous</a></li>";
    }
    for ($i = $startpage; $i <= $endpage; $i++) {
        if ($i == $page) {
            echo "<li class='active'><a href='{$url}?page=$i&{$urladd}'>$i</a></li>";
        } else {
            echo "<li><a href='{$url}?page=$i&{$urladd}'>$i</a></li>";
        }
    }
    if ($page == $lastpage) {
        echo "<li class='disabled'><a href='javascript:;'>Next</a></li>";
    } else {
        echo "<li><a href='{$url}?page=$nextpage&{$urladd}'>Next</a></li>";
    }
    echo "<li><a href='{$url}?page=$lastpage&{$urladd}'>Last</a></li>";
    echo "</ul>";
    echo "</nav>";
}

function test_input($data) {
    $data = trim($data);
    $data = htmlspecialchars($data);
    return $data;
}

function formatToFloatScore($score) {
    return floatval(sprintf("%.1f", $score));
}

function sqlInjectionFilter() {
    array_walk($_GET, function (&$v) {
        $v = sqlFilter($v);
    });
    array_walk($_POST, function (&$v) {
        $v = sqlFilter($v);
    });
}

function sqlFilter($value) {
    $filter = '/mysql[\s|\`]*\.[\s|\`]*(columns_priv|proc|tables_priv|user)|(or|and)\s*\d\s*=\s*\d|drop\s+table|select.*(from|load_file).*|insert\s+into\s|delete\s+from\s|truncate\s+\w+($|\s)|UNION\s+SELECT/i';
    $value = preg_replace($filter, ' ', $value);
    return $value;
}

function checkScore($score) {
    if (is_null($score)) {
        return '';
    } else if ($score < 0) {
        return "<span class='label label-default'>还未提交</span>";
    } else {
        return $score;
    }
}

function dbg($vars) {
    if (defined("IS_DEBUG") && IS_DEBUG) {
        dump($vars);
        echo "<hr/>";
    }
}

function ddbg($vars) {
    if (defined("IS_DEBUG") && IS_DEBUG) {
        dump($vars);
        echo "<hr/>";
        exit;
    }
}

