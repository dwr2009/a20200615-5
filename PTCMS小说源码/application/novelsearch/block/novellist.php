<?php
class novellistBlock extends Block
{
    public function run($zym_10)
    {
        if (isset($zym_10['novelid'])) {
            $zym_9 = explode('|', $zym_10['novelid']);
            foreach ($zym_9 as $zym_8 => $zym_14) {
                $zym_9[$zym_8] = dc::get('novelsearch_info', $zym_14);
            }
            return $zym_9;
        } else {
            if (isset($zym_10['novelname'])) {
                $zym_9 = explode('|', $zym_10['novelname']);
                $zym_7 = (array) M('novel_info')->where(array('name' => array('in', $zym_9), 'status' => 1))->getfield('name,id', true);
                foreach ($zym_9 as $zym_8 => $zym_14) {
                    $zym_13 = isset($zym_7[$zym_14]) ? $zym_7[$zym_14]['id'] : M('novel_info')->limit(1)->getfield('id');
                    $zym_9[$zym_8] = dc::get('novelsearch_info', $zym_13);
                }
                return $zym_9;
            } else {
                if (empty($zym_10['sort']) || !in_array($zym_10['sort'], array('lastupdate', 'postdate', 'allvisit', 'monthvisit', 'weekvisit', 'dayvisit', 'marknum', 'rand', 'votenum', 'downnum'))) {
                    $zym_10['sort'] = 'allvisit';
                } elseif ($zym_10['sort'] === 'rand') {
                    $zym_10['sort'] = 'rand()';
                }
                $zym_12 = array('status' => 1);
                if (isset($zym_10['category'])) {
                    if (strpos($zym_10['category'], '|')) {
                        $zym_12['categoryid'] = array('in', explode('|', $zym_10['category']));
                    } elseif (is_numeric($zym_10['category'])) {
                        $zym_12['categoryid'] = intval($zym_10['category']);
                    } else {
                        $zym_12['categoryid'] = M('category')->getinfoByKey($zym_10['category'], 'id');
                    }
                }
                if (isset($zym_10['isgood'])) {
                    $zym_12['isgood'] = intval($zym_10['isgood']);
                }
				if (isset($zym_10['isover'])) {
                    $zym_12['isover'] = intval($zym_10['isover']);
                }
                if (isset($zym_10['siteid'])) {
                    $zym_12['siteid'] = intval($zym_10['siteid']);
                }
                $zym_5 = 'desc';
                if (isset($zym_10['sort']) && $zym_10['sort'] == 'asc') {
                    $zym_5 = 'asc';
                }
                $zym_6 = isset($zym_10['num']) ? intval($zym_10['num']) : 10;
                $zym_11 = M('novelsearch_info')->where($zym_12)->limit($zym_6)->order("{$zym_10['sort']} {$zym_5}")->getlist();
                return $zym_11;
            }
        }
    }
}