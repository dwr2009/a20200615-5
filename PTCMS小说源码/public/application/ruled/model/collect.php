<?php
class CollectModel extends Collect_BaseModel
{
    public function __construct($zym_53, $zym_35 = 'ruleid')
    {
        parent::__construct();
        if ($zym_35 == 'siteid') {
            $zym_53 = (new RuleModel())->where(['siteid' => $zym_53])->order('update_time desc')->getfield('id');
        }
        $zym_36 = new ruledModel();
        $this->rule = $zym_36->getrule($zym_53);
    }
    protected function replacecontent($zym_52)
    {
        $zym_51 = trim($this->config->get('content_replace', ''));
        if ($zym_51) {
            $zym_28 = explode("\n", $zym_51);
            foreach ($zym_28 as $zym_29) {
                $zym_29 = trim($zym_29);
                $zym_36 = explode('|||', $zym_29);
                if (!isset($zym_36['1'])) {
                    $zym_36['1'] = '';
                }
                $zym_52 = str_replace($zym_36['0'], $zym_36['1'], $zym_52);
            }
        }
        return $zym_52;
    }
    public function getlist($zym_36)
    {
        $this->content = collect::getcontent($zym_36);
        $zym_30 = collect::getMatchAll($this->rule['list_novelid'], $this->content);
        $zym_31 = collect::getMatchAll($this->rule['list_novelname'], $this->content);
        //$zym_38 = collect::getMatchAll($this->rule['list_lastid'], $this->content);
        if (count($zym_30) > 0 && count($zym_30) == count($zym_31) ) {
            $zym_39 = $this->mergeParam(array('novelid' => $zym_30, 'novelname' => $zym_31));
            return $this->success($zym_39);
        } else {
            return $this->error('书号、书名、更新标识获取到的数量不一致 novelid' . count($zym_30) . ' novelname:' . count($zym_31) . ' 列表页内容长度:' . strlen($this->content));
        }
    }
    public function getinfo($zym_36)
    {
        $this->content = collect::getcontent($zym_36);
        $zym_54['novelname'] = novel::clearNovelName(collect::getMatch($this->rule['info_novelname'], $this->content));
        if (!$zym_54['novelname']) {
            return $this->error('采集小说名称失败，内容长度：' . strlen($this->content) . " url:" . $zym_36['rule'] . ' ' . $this->content);
        }
        $zym_54['category'] = trim(strip_tags(collect::getMatch($this->rule['info_category'], $this->content)));
        $zym_54['categoryid'] = novel::getcategory($zym_54['category']);
        $zym_54['author'] = novel::clearAuthorName(collect::getMatch($this->rule['info_author'], $this->content));
        $zym_54['isovercaption'] = trim(strip_tags(collect::getMatch($this->rule['info_isover'], $this->content)));
        $zym_54['cover'] = collect::parseUrl(collect::getMatch($this->rule['info_cover'], $this->content), $zym_36['rule']);
        if (C('collect_cover_check')) {
            $zym_54['cover'] = novel::checkCover($zym_54['cover']);
        }
        $zym_54['intro'] = novel::introformat(collect::getMatch($this->rule['info_intro'], $this->content));
        $zym_54['isover'] = (int) preg_match('{' . $this->config->get('collect_over_caption') . '}', $zym_54['isovercaption']);
        return $this->success($zym_54);
    }
    public function getdir($zym_36)
    {
        $this->content = collect::getcontent($zym_36);
        //$zym_47 = collect::getMatchAll($this->rule['dir_chapterid'], $this->content);
        $zym_48 = collect::getMatchAll($this->rule['dir_chaptername'], $this->content);
        $zym_45 = collect::getMatchAll($this->rule['dir_chapterurl'], $this->content);
        if (count($zym_48) != count($zym_45)) {
            return $this->error('章节id和章节名称数量不一致:  类型:' . count($zym_48) . ' 地址:' . count($zym_45));
        } else {
            $zym_55 = [];
            foreach ($zym_48 as $zym_40 => $zym_59) {
                $zym_55[] = ['sort' => strip_tags(htmlspecialchars_decode(strip_tags($zym_48[$zym_40]))), 'durl' => collect::parseUrl($zym_45[$zym_40], $zym_36['rule'])];
            }
            return $this->success($zym_55);
        }
    }
    public function getchapter($zym_42, $zym_27 = 0)
    {
        if ($zym_27 > 0 && $this->config->get('chapter_cache_power', 0)) {
            $zym_49 = $this->config->get('chapter_path');
            $zym_49 = str_replace('@', PT_ROOT, $zym_49);
            $zym_58 = md5($zym_42);
            $zym_11 = $zym_49 . '/greencache/' . date('Ym', $zym_27) . '/' . date('dH', $zym_27) . '/' . $zym_58[0] . $zym_58[1] . '/' . $zym_58[2] . $zym_58[3] . '/' . $zym_58 . '.txt';
            if (is_file($zym_11)) {
                $zym_12 = F($zym_11);
                if (mb_strlen($zym_12) > 100) {
                    return $this->success($zym_12);
                }
            }
            $zym_50 = $this->_getchapter($zym_42);
            if ($zym_50['status'] == 1) {
                if (mb_strlen($zym_50['data']) > 100) {
                    F($zym_11, $zym_50['data']);
                }
                return $zym_50;
            } else {
                return $this->error($zym_50['msg']);
            }
        } else {
            return $this->_getchapter($zym_42);
        }
    }
    protected function _getchapter($zym_42)
    {
        $zym_36 = $this->rule['chapter_api'];
        if ($this->rule['chapter_api']['rule']) {
            $zym_36['rule'] = str_replace('[url]', $zym_42, $zym_36['rule']);
        } else {
            $zym_36['rule'] = $zym_42;
        }
        $this->content = collect::getcontent($zym_36['rule']);
        $zym_12 = collect::getMatch($this->rule['chapter_content'], $this->content);
        $zym_12 = novel::chapterformat($zym_12);
        $zym_12 = $zym_12 ? $this->replacecontent($zym_12) : '';
        return $this->success($zym_12);
    }
    public function test()
    {
        $this->progress('开始采集', 'success', true);
        $zym_36 = $this->rule['url_list'];
        $zym_36['rule'] = str_replace('[页码]', '1', $zym_36['rule']);
        $this->progress('采集地址：' . $zym_36['rule'], 'info', true);
        $zym_55 = $this->getlist($zym_36);
        if ($zym_55['status'] == 1) {
            foreach ($zym_55['data'] as $zym_59) {
                echo "小说：{$zym_59['novelname']}({$zym_59['novelid']}) <br />";
            }
        } else {
            $this->progress($zym_55['msg'], 'error', true);
            exit;
        }
        $zym_14 = $zym_55['data']['0'];
        $zym_36 = $this->rule['url_info'];
        $zym_36['rule'] = str_replace(['[novelid]', '[subnovelid]'], [$zym_14['novelid'], subid($zym_14['novelid'])], $zym_36['rule']);
        $this->progress("<br/>采集小说：{$zym_14['novelname']} 书号{$zym_14['novelid']}", 'info', true);
        $this->progress('采集信息地址：' . $zym_36['rule'], 'info', true);
        $zym_56 = $this->getinfo($zym_36);
        if ($zym_56['status'] == 1) {
            $this->progress("&nbsp;&nbsp;&nbsp;&nbsp;- 书名： {$zym_56['data']['novelname']}", 'info');
            $this->progress("&nbsp;&nbsp;&nbsp;&nbsp;- 作者： {$zym_56['data']['author']}", 'info');
            $this->progress("&nbsp;&nbsp;&nbsp;&nbsp;- 封面： {$zym_56['data']['cover']}", 'info');
            $this->progress("&nbsp;&nbsp;&nbsp;&nbsp;- 分类： {$zym_56['data']['category']}(id:{$zym_56['data']['categoryid']})", 'info');
            $this->progress("&nbsp;&nbsp;&nbsp;&nbsp;- 进度： {$zym_56['data']['isovercaption']}(值:{$zym_56['data']['isover']})", 'info');
            $this->progress("&nbsp;&nbsp;&nbsp;&nbsp;- 简介： <br/>" . showintro($zym_56['data']['intro']), 'info');
        } else {
            $this->progress($zym_56['msg'], 'error', true);
            exit;
        }
        $zym_36 = $this->rule['url_dir'];
        $zym_36['rule'] = str_replace(['[novelid]', '[subnovelid]'], [$zym_14['novelid'], subid($zym_14['novelid'])], $zym_36['rule']);
        $this->progress("<br/>采集小说目录：{$zym_36['rule']} ", 'info', true);
        $zym_9 = $this->getdir($zym_36);
        if ($zym_9['status'] == 1) {
            foreach ($zym_9['data'] as $zym_59) {
                $this->progress("&nbsp;&nbsp;&nbsp;&nbsp;- {$zym_59['sort']} - {$zym_59['durl']})", 'info');
            }
        } else {
            $this->progress($zym_9['msg'], 'error', true);
            exit;
        }
//        $zym_5 = $zym_9['data']['0'];
//        $this->progress("<br/>采集类型：{$zym_5['sort']} 地址:{$zym_5['durl']}", 'info', true);
//        $zym_6 = $this->getchapter($zym_5['chapterurl']);
//        if ($zym_6['status'] == 1) {
//            $this->progress('章节正文：<br/>' . showchapter($zym_6['data']), 'info');
//        } else {
//            $this->progress($zym_6['msg'], 'error', true);
//            exit;
//        }
    }
    public function collectlist($zym_42 = '', $zym_7 = 1, $zym_8 = 0)
    {
        $zym_13 = new NovelSearch_LogModel();
        $zym_26 = $this->rule['url_info'];
        $zym_22 = $this->rule['url_dir'];
        $zym_15 = $this->rule['url_info']['rule'];
        $zym_24 = $this->rule['url_dir']['rule'];
        $this->progress("开始采集" . date('Y-m-d H:i:s'), 'success', true);
      
        if ($zym_42 == '') {
            $zym_42 = $this->rule['url_list']['rule'];
        }
        if ($zym_8) {
            $zym_25 = cache::get("cron_{$zym_8}_pid");
        }
		$zym_36 = $this->rule['url_list'];
        $zym_36['rule'] = str_replace('[页码]', $zym_7, $zym_42);
        $this->progress("采集地址:" . $zym_36['rule'], 'success', true);
      
        $zym_50 = $this->getlist($zym_36);
     
        if ($zym_50['status'] == 1) {
            foreach ($zym_50['data'] as $zym_40 => $zym_59) {
                if ($zym_8) {
                    if ($zym_25 != cache::get("cron_{$zym_8}_pid")) {
                        exit('diff pid');
                    }
                    if (!M('cron')->getpower()) {
                        exit('cron stop');
                    }
                    cache::set("cron_{$zym_8}_novel", $zym_59['novelname']);
                }
                
              $zym_21 = $zym_13->where(['fromid' => $zym_59['novelid'], 'siteid' => $this->rule['siteid']])->find();
            
                if ($zym_21) {
                    $this->progress("《{$zym_59['novelname']}》获取采集进度成功");
                } else {
                    $zym_26['rule'] = str_replace(['[novelid]', '[subnovelid]'], [$zym_59['novelid'], subid($zym_59['novelid'])], $zym_15);
                    $zym_26['fromid'] = $zym_59['novelid'];
                   if (!($zym_21 = $this->addlog($zym_26))) {
                      continue;
                    }
                }
             
                    $this->progress("《{$zym_59['novelname']}》开始处理章节");
           
                    $zym_22['rule'] = str_replace(['[novelid]', '[subnovelid]'], [$zym_59['novelid'], subid($zym_59['novelid'])], $zym_24);
            
                    $zym_20 = $this->getnewChapter($zym_22, $zym_21);
          
             
            
                    if ($zym_20 && count($zym_20) >= 1) {
                        $this->progress("《{$zym_59['novelname']}》需要处理章节数目：" . count($zym_20));
                      
                        $this->updateNovel($zym_20, $zym_21);
      
                        $this->progress("《{$zym_59['novelname']}》更新小说完成");
                    } else {
                        $this->progress("《{$zym_59['novelname']}》没有新章节,不需要更新");
                    }
                if ($zym_8) {
                    cache::rm("cron_{$zym_8}_novel");
                }
            }
        } else {
            $this->progress($zym_50['msg'], 'error', true);
        }
        $this->progress("<br >采集完成!" . date('Y-m-d H:i:s'), 'success', true);
    }
    public function collectidone($zym_57, $zym_17 = true)
    {
        if ($zym_17) {
            $this->progress("开始采集" . date('Y-m-d H:i:s'), 'success', true);
        }
        $zym_26 = $this->rule['url_info'];
        $zym_22 = $this->rule['url_dir'];
        $zym_15 = $this->rule['url_info']['rule'];
        $zym_24 = $this->rule['url_dir']['rule'];
        $zym_13 = new NovelSearch_LogModel();
        $zym_21 = (array) $zym_13->where(['fromid' => $zym_57, 'siteid' => $this->rule['siteid']])->find();
        if ($zym_21) {
            $zym_31 = $this->model->get('novelsearch_info', $zym_21['novelid'], 'novel.name');
            if (!$zym_31) {
                $this->progress('小说不存在，本站书号：' . $zym_21['novelid'], 'error');
                return;
            }
            $this->progress("《{$zym_31}》获取采集进度成功");
        } else {
            $zym_26['rule'] = str_replace(['[novelid]', '[subnovelid]'], [$zym_57, subid($zym_57)], $zym_15);
            $zym_26['fromid'] = $zym_57;
            $this->progress('目标站书号：' . $zym_57 . '未采集过,创建采集记录');
            $zym_21 = $this->addlog($zym_26);
            if ($zym_21) {
                $zym_31 = $this->model->flush('novelsearch_info', $zym_21['novelid'], 'novel.name');
                if (!$zym_31) {
                    $this->progress('小说不存在，书号：' . $zym_21['novelid'], 'error');
                    return;
                } else {
                    $this->progress("《{$zym_31}》添加新书成功");
                }
            }
        }
        if ($zym_21) {
            $this->progress("《{$zym_31}》开始处理章节");
            $zym_22['rule'] = str_replace(['[novelid]', '[subnovelid]'], [$zym_57, subid($zym_57)], $zym_24);
            $zym_20 = $this->getnewChapter($zym_22, $zym_21);
            if ($zym_20) {
                $this->progress("《{$zym_31}》需要处理章节数目：" . count($zym_20));
                $this->updateNovel($zym_20, $zym_21);
                $this->progress("《{$zym_31}》更新小说完成");
            } else {
                $this->progress("《{$zym_31}》不需要更新");
            }
        }
        if ($zym_17) {
            $this->progress("<br >采集完成!" . date('Y-m-d H:i:s'), 'success', true);
        }
    }
    public function collectid($zym_18, $zym_19 = '')
    {
        $this->progress("开始采集" . date('Y-m-d H:i:s'), 'success', true);
        $zym_26 = $this->rule['url_info'];
        $zym_22 = $this->rule['url_dir'];
        $zym_15 = $this->rule['url_info']['rule'];
        $zym_24 = $this->rule['url_dir']['rule'];
        $zym_13 = new NovelSearch_LogModel();
        for ($zym_57 = $zym_18; $zym_57 < $zym_19; $zym_57++) {
            $zym_21 = (array) $zym_13->where(['fromid' => $zym_57, 'siteid' => $this->rule['siteid']])->find();
            if ($zym_21) {
                $zym_31 = $this->model->get('novelsearch_info', $zym_21['novelid'], 'novel.name');
                if (!$zym_31) {
                    $this->progress('小说不存在，本站书号：' . $zym_21['novelid'], 'error');
                    continue;
                }
                $this->progress("《{$zym_31}》获取采集进度成功");
            } else {
                $zym_26['rule'] = str_replace(['[novelid]', '[subnovelid]'], [$zym_57, subid($zym_57)], $zym_15);
                $zym_26['fromid'] = $zym_57;
                $this->progress('目标站书号：' . $zym_21['novelid'] . '未采集过,创建采集记录');
                $zym_21 = $this->addlog($zym_26);
                if ($zym_21) {
                    $zym_31 = $this->model->get('novelsearch_info', $zym_21['novelid'], 'novel.name');
                    if (!$zym_31) {
                        $this->progress('小说不存在，书号：' . $zym_21['novelid'], 'error');
                        continue;
                    } else {
                        $this->progress("《{$zym_31}》添加新书成功");
                    }
                }
            }
            if ($zym_21) {
                $this->progress("《{$zym_31}》开始处理章节");
                $zym_22['rule'] = str_replace(['[novelid]', '[subnovelid]'], [$zym_57, subid($zym_57)], $zym_24);
                $zym_20 = $this->getnewChapter($zym_22, $zym_21);
                if ($zym_20) {
                    $this->progress("《{$zym_31}》需要处理章节数目：" . count($zym_20));
                    $this->updateNovel($zym_20, $zym_21);
                    $this->progress("《{$zym_31}》更新小说完成");
                } else {
                    $this->progress("《{$zym_31}》不需要更新");
                }
            }
        }
        $this->progress("<br >采集完成!" . date('Y-m-d H:i:s'), 'success', true);
    }
    protected function addlog($zym_36)
    {
        static $skipNovel;
        if (!$skipNovel) {
            $skipNovel = explode('|', str_replace("\n", '|', $this->pt->config->get('collect_skip_novel')));
        }
        $zym_50 = $this->getinfo($zym_36);
        if ($zym_50['status'] == 1) {
            $zym_43 = new NovelSearch_infoModel();
            $zym_16 = $zym_43->field('id,siteid')->where(['name' => $zym_50['data']['novelname'], 'author' => $zym_50['data']['author']])->find();
            if (!$zym_16) {
                if ($this->rule['addnew']) {
                    if (in_array($zym_50['data']['novelname'], $skipNovel)) {
                        $this->progress("《{$zym_50['data']['novelname']}》在屏蔽列表，不进行采集。" . $zym_43->getError(), 'error');
                        return false;
                    }
                    if (!$zym_50['data']['novelname'] || !$zym_50['data']['author']) {
                        $this->progress("《{$zym_50['data']['novelname']}》(作者:《{$zym_50['data']['author']}》) 有一项不完整,跳过添加新书。", 'error');
                        return false;
                    }
                    $zym_30 = $zym_43->add(['name' => $zym_50['data']['novelname'], 'author' => $zym_50['data']['author'], 'cover' => $zym_50['data']['cover'], 'categoryid' => $zym_50['data']['categoryid'], 'intro' => $zym_50['data']['intro'], 'isover' => $zym_50['data']['isover'], 'siteid' => $this->rule['siteid']]);
                    if ($zym_30) {
                        $this->progress("《{$zym_50['data']['novelname']}》添加新书成功，书号{$zym_30}");
                    } else {
                        $this->progress("《{$zym_50['data']['novelname']}》添加新书失败。" . $zym_43->getError(), 'error');
                        return false;
                    }
                } else {
                    $this->progress("《{$zym_50['data']['novelname']}》(作者:{$zym_50['data']['author']})本站不存在，规则不添加新书，跳过", 'warning');
                    return false;
                }
            } else {
                $zym_30 = $zym_16['id'];
                if ($this->rule['newreplace'] && $zym_16['siteid'] > 0 && $zym_16['siteid'] != $this->rule['siteid']) {
                    $zym_43->where(['id' => $zym_30])->update(['cover' => $zym_50['data']['cover'], 'categoryid' => $zym_50['data']['categoryid'], 'intro' => $zym_50['data']['intro'], 'isover' => $zym_50['data']['isover'], 'siteid' => $this->rule['siteid']]);
                    $this->progress("《{$zym_50['data']['novelname']}》更新信息成功");
                }
            }
            $zym_10 = ['novelid' => $zym_30, 'siteid' => $this->rule['siteid'], 'fromid' => $zym_36['fromid'], 'lastid' => 0];
            if ($zym_23 = (new NovelSearch_LogModel())->insert($zym_10)) {
                $this->progress("《{$zym_50['data']['novelname']}》添加日志成功");
                return $zym_10;
            } else {
                $this->progress("《{$zym_50['data']['novelname']}》添加日志失败", 'error');
                return false;
            }
        } else {
            $this->progress('采集失败:' . $zym_50['msg'], 'error');
            return false;
        }
    }
    protected function getnewChapter($zym_36, $zym_21)
    {
        $zym_50 = $this->getdir($zym_36);
        if ($zym_50['status'] == 1) {
            if ($zym_21['lastid'] == 0) {
                if ($this->rule['collectallchapter']) {
                    $zym_41 = $zym_50['data'];
                } else {
                    $zym_41 = [end($zym_50['data'])];
                }
            } else {
                $zym_41 = [];
                $zym_44 = (array) (new NovelSearch_chapterModel())->setTableId($zym_21['novelid'])->where(['novelid' => $zym_21['novelid'], 'siteid' => $this->rule['siteid']])->getField('url', true);
                foreach ($zym_50['data'] as $zym_40 => $zym_59) {
                    if ($zym_21['lastid'] < $zym_59['chapterid']) {
                        if (!in_array($zym_59['chapterurl'], $zym_44)) {
                            $zym_41[] = $zym_59;
                        }
                    }
                }
            }
        } else {
            $this->progress('采集失败:' . $zym_50['msg'], 'error');
            return false;
        }
        return $zym_41;
    }
    protected function updateNovel($zym_5, $zym_21)
    {
        $zym_46 = array();
        $zym_16 = $this->model->get('novelsearch_info', $zym_21['novelid']);
      
        $zym_32 = ['novelid' => $zym_21['novelid'], 'siteid' => $zym_21['siteid']];
     
        foreach ($zym_5 as $zym_59) {
            $zym_32['type'] = $zym_59['sort'];
            $zym_32['url'] = $zym_59['durl'];
            $zym_32['time'] = time();
            $zym_46[] = $zym_32;
        }
        $zym_33 = (new novelsearch_downModel())->addall($zym_46);

       
        if ($zym_33 === 0) {
            $this->progress("《{$zym_16['novel']['name']}》过滤后不需要增加章节", 'error');
            return;
        } elseif ($zym_33 === false) {
            $this->progress("《{$zym_16['novel']['name']}》写章节数据失败!" . M('novelsearch_chapter')->geterror(), 'error');
            return;
        }
      //  $zym_37 = end($zym_5);
//        M('novelsearch_log')->where(array('novelid' => $zym_21['novelid'], 'siteid' => $zym_21['siteid'], 'fromid' => $zym_21['fromid']))->update(array('lastid' => $zym_37['chapterid']));
//        $zym_34 = array('lastchapterid' => $zym_33, 'lastchaptername' => $zym_32['name'], 'lastsiteid' => $zym_32['siteid'], 'lastupdate' => time());
        if ($this->rule['addnew']) {
            if ($zym_16['source']['siteid'] == '9999') {
                $zym_34['siteid'] = $zym_21['siteid'];
                $zym_34['orderid'] = 0;
                $zym_34['chaptersign'] = $zym_33;
                $zym_36 = $this->rule['url_info'];
                $zym_36['rule'] = str_replace(['[novelid]', '[subnovelid]'], [$zym_21['novelid'], subid($zym_21['novelid'])], $zym_36['rule']);
                $zym_56 = $this->getinfo($zym_36);
                $zym_34['isover'] = $zym_56['status'];
            } else {
                if ($zym_16['source']['siteid'] == $zym_21['siteid']) {
                    $zym_34['chaptersign'] = $zym_33;
                    $zym_36 = $this->rule['url_info'];
                    $zym_36['rule'] = str_replace(['[novelid]', '[subnovelid]'], [$zym_21['novelid'], subid($zym_21['novelid'])], $zym_36['rule']);
                    $zym_56 = $this->getinfo($zym_36);
                    $zym_34['isover'] = $zym_56['status'];
                }
            }
        }
        //M('novelsearch_info')->where(array('id' => $zym_21['novelid']))->update($zym_34);
        $this->model->rm('novelsearch_info', $zym_21['novelid']);
    }
    public function cron($zym_8)
    {
        if ($this->rule) {
            $this->collectlist('', 1, $zym_8);
        } else {
            $this->error('找不到指定的规则');
        }
    }
}
?>