<?php
/**
 * Created by PhpStorm.
 * User: invokerx
 * Date: 2018/3/13
 * Time: 下午4:24
 */

namespace Dwin\Model;


use Think\Model;

class OrderCollectionModel extends Model
{
    protected $settlePriceField = "(case
                                        when b.settlement_method in ('JF05', 'JF16') then settle_price * 0.99
                                        when b.settlement_method in ('HP01', 'HP02') then settle_price * 0.98
                                        else settle_price end)";
    protected $productNumField = "case
                                        when p.statistics_shipments_flag = 1 
                                            then 
                                                (case 
                                                    when settle_type in (1,7) 
                                                        then product_num
                                                    when settle_type in (3) 
                                                        then CAST(product_num AS SIGNED) * (-1)
                                                    else 0 end)
                                        else 0 end";
    protected $settleAllField = "case  
                                    when a.product_id = 15000  
                                        then settle_price
                                    else case 
                                            when settle_type in (1,2,7) 
                                                then settle_price
                                            when settle_type in (3) 
                                                then settle_price * (-1)
                                            else 0 end
                                    end";
    protected $partsNumField = "case
                                    when p.platform_id = 4 
                                        then 
                                            (case when settle_type in (1,7) then product_num
                                               when settle_type in (3) then CAST(product_num AS SIGNED) * (-1)
                                               else 0 end)
                                    else 0 end";
    protected $settleBackFeeField = "case
                                        when settle_type = 3 and a.product_id != 15000 
                                            then settle_price
                                        else 0 end";

    protected $settlePreFeeField;
    protected $settleResearchField;
    protected $settleCartographyFee;
    protected $settleMaintenanceFee;
    protected $settleShippingFee;
    protected $settlePerformance;
    protected $settleWorthyPerform;
    protected $settleMarketPerform;
    protected $repaymentBack;
    protected $repaymentSale;
    protected $repaymentPerform;
    public function _initialize()
    {
        $this->settlePreFeeField = "case when a.product_id = 15003 then" . $this->settlePriceField . " else 0 end";
        $this->settleCartographyFee = "case when a.product_id = 15001 then {$this->settlePriceField} else 0 end";
        $this->settleMaintenanceFee = "case when a.product_id = 15004 then {$this->settlePriceField} else 0 end";
        $this->settleShippingFee = "case when a.product_id = 15000 then settle_price else 0 end";
        $this->settlePerformance = "case
                                        when settle_type in (1,7) and a.product_id not in (15003) and p.statistics_performance_flag = 1 
                                            then 
                                                {$this->settlePriceField}
                                        when settle_type in (3) and a.product_id not in (15003) and p.statistics_performance_flag = 1
                                            then 
                                                {$this->settlePriceField}* (-1)
                                        else 0 end";
        $this->settleWorthyPerform = "case 
                                        when settle_type in (1,7) and a.product_id not in (15000,15001,15002,15003,15004) and q.id = 3 and p.statistics_performance_flag = 1 
                                            then 
                                                {$this->settlePriceField}
                                        when settle_type in (3) and a.product_id not in (15000,15001,15002,15003,15004) and q.id = 3 and p.statistics_performance_flag = 1
                                            then 
                                                {$this->settlePriceField} * (-1)
                                        else 0 end";
        $this->settleMarketPerform = "case
                                        when settle_type in (1,7) and a.product_id not in (15000,15001,15002,15003,15004) and q.id = 2 and p.statistics_performance_flag = 1 
                                            then 
                                                {$this->settlePriceField}
                                        when settle_type in (3) and a.product_id not in (15000,15001,15002,15003,15004) and q.id = 2 and p.statistics_performance_flag = 1
                                            then 
                                                {$this->settlePriceField} * (-1)
                                        else 0 end";
        $this->settleResearchField = "case when a.product_id = 15002 then {$this->settlePriceField} else 0 end";
        $this->repaymentBack = "case when a.product_id = 15000 then 0 when settle_type = 6 then settle_price else 0 end";
        $this->repaymentSale = "case when p.platform_id = 6 then 0 when settle_type = 8 and p.platform_id != 6 then settle_price else 0 end";
        $this->repaymentPerform = "case when p.platform_id = 6 then 0 when settle_type = 9 and p.platform_id != 6 then settle_price else 0 end";
    }

    /**
     * @name getCollectionData
     * 读取结算信息数据（详情，不分组查询）表别名情况：order_collection as a,orderform as b,customer as c, staff as d,dept as e,invoice as f, settlementlist as g, collection_type as h performance_type as q
     * @param string $field 查询的字段
     * @param mixed $where where查询条件
     * @param string $order 查询的order排序条件
     * @param string $start limit查询的起始位置
     * @param string $length limit查询的长度
     * @return array ￥$data 返回符合条件的数据
    */
    public function getCollectionData($field, $where, $order, $start, $length)
    {
        $data = $this->field($field)
            ->alias('a')
            ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
            ->join('LEFT JOIN crm_customer c ON b.cus_id = c.cid')
            ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
            ->join('LEFT JOIN crm_dept e ON e.id = d.deptid')
            ->join('LEFT JOIN crm_order_invoice f ON f.type_id    = b.invoice_type')
            ->join('LEFT JOIN crm_settlementlist g ON g.settle_id = b.settlement_method')
            ->join('LEFT JOIN crm_order_collection_type h ON h.id = a.settle_type')
            ->join('LEFT JOIN crm_order_performance_type q ON q.type_id = b.static_type')
            ->join('LEFT JOIN crm_staff i ON i.id = a.settle_id')
            ->where($where)
            ->order($order)
            ->limit($start, $length)
            ->select();
        return $data;
    }

    public function getCollectionDataWithGroup($field, $where, $order, $start, $length, $group)
    {
        $data = $this->alias('a')
            ->field($field)
            ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
            ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
            ->join('LEFT JOIN crm_dept e ON e.id = d.deptid')
            ->join('LEFT JOIN crm_material p ON p.product_id = a.product_id')
            ->join('LEFT JOIN crm_customer k ON k.cid = b.cus_id')
            ->where($where)
            ->order($order)
            ->limit($start, $length)
            ->group($group)
            ->select();
        return $data;
    }

    /**
     * @name getCollectionNumber
     * @abstract 获取满足条件的结算信息数量
     * @param array $where 条件
     * @param string $countString 要统计计数的字段
     * @return int $count
     */
    public function getCollectionNumber($where, $countString)
    {
        $count = $this->where($where)->count($countString);
        return $count;
    }

    public function getCollectionByFind($field, $where, $order)
    {
       $data = $this->field($field)
            ->join('LEFT JOIN crm_orderform c ON c.id = crm_order_collection.cus_order_id')
            ->where($where)
            ->order($order)
            ->find();// 原来的未修改数据
        return $data;
    }

    /**
     * @name editCollectionData
     * @abstract 结算信息编辑
     * @param array $where 条件
     * @param array $updateData 更新的数据（key为字段名 value为变更的数据）
     * @return boolean $rst;
     */
    public function editCollectionData($where, $updateData)
    {
        return $rst = $this->where($where)->setField($updateData);
    }


    public function getStatisticsAmount($map)
    {
        $field = "count(distinct b.cpo_id) order_num_all,
                    round(sum({$this->productNumField})) product_nums,
                    /*settle_type = 3 退货 settle_all_price 结算总金额（运费+收费-退款）*/
                    round(sum({$this->settleAllField}),2) settle_all_price,
                    /*settle_back_price 退货总金额（结算方式为退款的所有金额 - 其中的运费）*/
                    round(sum({$this->settleBackFeeField}),2) settle_back_price,
                    /*settle_pre_price 预收款总金额（订单中填写产品为预收款的所有金额）*/
                    round(sum($this->settlePreFeeField),2) settle_pre_price,
                    /*settle_research_price 研发费总金额（研发费id总和）*/
                    round(sum($this->settleResearchField),2) settle_research_price,
                    /*cartography 制图费*/
                    round(sum($this->settleCartographyFee),2) cartography_fee,
                    /*maintenance_fees 维修费*/
                    round(sum($this->settleMaintenanceFee),2) maintenance_fees,
                    round(sum($this->settleShippingFee),2) shipping_costs,
                    round(sum($this->settlePerformance),2) settle_normal_price,
                    round(sum($this->settleWorthyPerform),2) value_price,
                    round(sum($this->settleMarketPerform),2) marketing_price,
                    round(sum($this->repaymentBack),2) back_price,
                    round(sum($this->repaymentSale),2) sale_price,
                    round(sum($this->repaymentPerform),2) performance_price";
        $rst = $this->alias('a')
            ->field($field)
            ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
            ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
            ->join('LEFT JOIN crm_dept e ON e.id = d.deptid')
            ->join('LEFT JOIN crm_material p ON p.product_id = a.product_id')
            ->join('LEFT JOIN crm_order_performance_type q ON q.type_id = b.static_type')
            ->where($map)
            ->select();
        return $rst[0];
    }

    public function getCusCount($map)
    {
        $data = $this->alias('a')
            ->where($map)
            ->field('b.cus_id')
            ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
            ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
            ->join('LEFT JOIN crm_customer m ON m.cid = b.cus_id')
            ->join('LEFT JOIN crm_industry k ON m.ctype = k.id')
            ->join('LEFT JOIN crm_staff j ON j.id = m.uid')
            ->join('LEFT JOIN crm_dept e ON e.id = j.deptid')
            ->group('b.cus_id')
            ->select();
        return count($data);
    }

    public function getCusStatisticsData($map,$sqlCondition, $field)
    {
        return $this->alias('a')
            ->field($field)
            ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
            ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
            ->join('LEFT JOIN crm_material p ON p.product_id = a.product_id')
            ->join('LEFT JOIN crm_order_performance_type q ON q.type_id = b.static_type')
            ->join('LEFT JOIN crm_customer m ON m.cid = b.cus_id')
            ->join('LEFT JOIN crm_staff j ON j.id = m.uid')
            ->join('LEFT JOIN crm_industry k ON m.ctype = k.id')
            ->join('LEFT JOIN crm_dept e ON e.id = j.deptid')
            ->where($map)
            ->group('b.cus_id')
            ->order($sqlCondition['order'])
            ->limit($sqlCondition['start'], $sqlCondition['length'])
            ->select();
    }


    public function getCusStatistics($config, $sqlCondition, $limitConfig)
    {

        $map['a.settle_time'] = [['egt', $config['startDay']], ['elt', $config['endDay']]];
        if ($limitConfig['industry']) {
            $map['m.ctype'] = ['eq', $limitConfig['industry']];
        }
        if ($limitConfig['staff']) {
            $map['m.uid'] = ['IN', $limitConfig['staff']];
        }
        if (!empty($limitConfig['kpi'])) {
            $map['m.kpi_flag'] = ['in', $limitConfig['kpi']];
        }
        $count = $this->getCusCount($map);
        if (trim($sqlCondition['search'])) {
            $map['e.name|b.cus_name|j.name'] = ['LIKE', "%" . trim($sqlCondition['search']) . "%"];
        }
        $filterCount = $this->getCusCount($map);

        $baseCondition = "case when a.settle_time > {$config['startDay']} and a.settle_time < {$config['endDay']} then b.cpo_id else null end";
        $settlePriceCondition = "case 
                                    when settle_type in (1,7) and a.product_id not in (15000,15001,15002,15003,15004) and p.statistics_performance_flag = 1 
                                        then {$this->settlePriceField}
                                    when settle_type in (3,8) and a.product_id not in (15000,15001,15002,15003,15004) and p.statistics_performance_flag = 1
                                        then {$this->settlePriceField} * (-1)
                                 else 0 end";
        $field = "";
        $field .= "e.name dept_name,    
                        b.cus_name,
                        k.name industry_name,
                        ifnull(j.name,'无') cus_pic_name,
                        count(distinct($baseCondition)) order_num_all,
                        sum($this->productNumField) product_nums,
                        round(sum(case when a.product_id = 15002 then {$this->settlePriceField} else 0 end),2) + 
                        round(sum(case when a.product_id = 15001 then {$this->settlePriceField} else 0 end),2) + 
                        round(sum(case when a.product_id = 15004 then {$this->settlePriceField} else 0 end),2) + 
                        round(sum($settlePriceCondition),2) settle_prices
                            ";
        if ($config['endYear'] >= $config['startYear']) {
            $tmpEndM = $config['endM'] + 12 * ($config['endYear'] - $config['startYear']);
        } else {
            $tmpEndM = $config['endM'];
        }

        for ($i = $config['startM']; $i < $tmpEndM; $i++) {
            $string  = "order_number" . (int)$i;
            $string2 = "product_nums" . (int)$i;
            $string3 = "settle_prices" . (int)$i;
            if ($config['endYear'] === $config['startYear']) {
                $tmpTimeString = strtotime($config['startYear'] . "-" . ($i));
            } else {
                $startYM = getYearMonth($config['startYear'], $i);
                $tmpTimeString = strtotime($startYM);
            }
            $tmpYear = date('Y', $tmpTimeString);
            $tmpM = date('m', $tmpTimeString);
            $y = $tmpM === 12 ? $tmpYear - 1 : $tmpYear;
            $m = $tmpM === 12 ? $tmpM - 1 + 12 : $tmpM - 1;
            $$string = strtotime($y . '-' . $m);
            $field .= ",";
            $condition = "a.settle_time >= {$$string} and a.settle_time < {$tmpTimeString}";

            $field .= "count(distinct(case when $condition then b.cpo_id else null end)) $string,
                        sum($this->productNumField) $string2,
                        round(sum(case when a.product_id = 15002 and $condition then {$this->settlePriceField} else 0 end),2) + 
                        round(sum(case when a.product_id = 15001 and $condition then {$this->settlePriceField} else 0 end),2) + 
                        round(sum(case when a.product_id = 15004 and $condition then {$this->settlePriceField} else 0 end),2) + 
                        round(
                        sum(case  
                            when settle_type in (1,7) and a.product_id not in (15000,15001,15002,15003,15004) and p.statistics_performance_flag = 1 and $condition
                                then 
                                    {$this->settlePriceField}
                            when settle_type in (3,8) and a.product_id not in (15000,15001,15002,15003,15004) and p.statistics_performance_flag = 1 and $condition
                                then 
                                    {$this->settlePriceField} * (-1)
                            else 0 end),2) {$string3}
                        ";
        }
        $data = $this->getCusStatisticsData($map, $sqlCondition, $field);
        return [$count, $filterCount, $data];
    }


    public function getCollectionStatistics($type, $map, $sqlCondition)
    {

        switch ($type) {
            case 2 :
                $field = "b.pic_name,
                    e.name dept,
                    count(distinct b.cpo_id) order_num_all,
                    round(sum({$this->productNumField})) product_nums,
                    /*settle_type = 3 退货 settle_all_price 结算总金额（运费+收费-退款）*/
                    round(sum({$this->settleAllField}),2) settle_all_price,
                    /*settle_back_price 退货总金额（结算方式为退款的所有金额 - 其中的运费）*/
                    round(sum({$this->settleBackFeeField}),2) settle_back_price,
                    /*settle_pre_price 预收款总金额（订单中填写产品为预收款的所有金额）*/
                    round(sum($this->settlePreFeeField),2) settle_pre_price,
                    /*settle_research_price 研发费总金额（研发费id总和）*/
                    round(sum($this->settleResearchField),2) settle_research_price,
                    /*cartography 制图费*/
                    round(sum($this->settleCartographyFee),2) cartography_fee,
                    /*maintenance_fees 维修费*/
                    round(sum($this->settleMaintenanceFee),2) maintenance_fees,
                    round(sum($this->settleShippingFee),2) shipping_costs,
                    round(sum($this->settlePerformance),2) settle_normal_price,
                    round(sum($this->settleWorthyPerform),2) value_price,
                    round(sum($this->settleMarketPerform),2) marketing_price,
                    round(sum($this->repaymentBack),2) back_price,
                    round(sum($this->repaymentSale),2) sale_price,
                    round(sum($this->repaymentPerform),2) performance_price";
                $data = $this->getCollectionStatData($map, $sqlCondition,$field);

                $count = $this->getCollectionStatCount($map);
                $filterCount = $this->getCollectionStatCount($map, $sqlCondition);
                break;
            case 3 :
                $field = "if (LENGTH(b.cus_name) < 7,b.cus_name, REPLACE(b.cus_name,SUBSTRING(b.cus_name,3,4),\"****\")) cus_name,
                    b.pic_name,
                    e.name dept,
                    count(distinct b.cpo_id) order_num_all,
                    sum($this->productNumField) product_nums,
                    round(sum($this->settleAllField),2) settle_all_price,
                    round(sum($this->settleBackFeeField),2) settle_back_price,
                    round(sum($this->settlePreFeeField),2) settle_pre_price,
                    round(sum($this->settleResearchField),2) settle_research_price,
                    round(sum($this->settleCartographyFee),2) cartography_fee,
                    round(sum($this->settleMaintenanceFee),2) maintenance_fees,
                    round(sum($this->settleShippingFee),2) shipping_costs,
                    round(sum($this->settlePerformance),2) settle_normal_price,
                    round(sum($this->settleWorthyPerform),2) value_price,
                    round(sum($this->settleMarketPerform),2) marketing_price,
                    round(sum($this->repaymentBack),2) back_price,
                    round(sum($this->repaymentSale),2) sale_price,
                    round(sum($this->repaymentPerform),2) performance_price";
                $data = $this->getCollectionStatData($map, $sqlCondition,$field);
                $count = $this->getCollectionStatCount($map);
                $filterCount = $this->getCollectionStatCount($map, $sqlCondition);
                break;
            default :
                $field = "a.id,
                    b.cpo_id order_id,
                    from_unixtime(a.settle_time) settle_time,
                    if (LENGTH(b.cus_name) < 7, b.cus_name, REPLACE(b.cus_name,SUBSTRING(b.cus_name,3,4),'****')) cus_name,
                    b.pic_name,
                    b.cpo_id k_order_id, 
                    a.product_name, 
                    a.single_price, 
                    a.product_num , 
                    a.settle_price, 
                    c.csource, 
                    f.invoice_name invoice_type,
                    e.name dept, 
                    g.settle_name settlement_name, 
                    h.collection_type settle_type,
                    q.performance_type_name performance_type";
                list($count, $filterCount, $data) = $this->getCollectionStatDetailData($map, $sqlCondition,$field);
                break;
        }

        return [$count, $filterCount, $data];
    }

    public function getCollectionStatData($map, $sqlCondition,$field)
    {
        return $this->alias('a')
            ->field($field)
            ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
            ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
            ->join('LEFT JOIN crm_dept e ON e.id = d.deptid')
            ->join('LEFT JOIN crm_material p ON p.product_id = a.product_id')
            ->join('LEFT JOIN crm_order_performance_type q ON q.type_id = b.static_type')
            ->where($map)
            ->group($sqlCondition['group'])
            ->limit($sqlCondition['start'], $sqlCondition['length'])
            ->order($sqlCondition['order'])
            ->select();
    }

    public function getCollectionStatCount($map, $sqlCondition = [])
    {
        $data = $this->alias('a')
        ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
        ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
        ->join('LEFT JOIN crm_dept e ON e.id = d.deptid')
        ->join('LEFT JOIN crm_material p ON p.product_id = a.product_id')
        ->join('LEFT JOIN crm_order_performance_type q ON q.type_id = b.static_type')
        ->where($map)->group($sqlCondition['group'])->select();
        return $count = count($data) ? count($data) : 0;
    }

    public function getCollectionStatDetailData($map, $sqlCondition, $field){
        $count = $this
            ->alias('a')
            ->join('left join crm_orderform b ON a.cus_order_id = b.id')
            ->where($map)
            ->count();
        $recordsFiltered = $count;
        $rst = $this
            ->alias('a')
            ->field($field)
            ->join('LEFT JOIN crm_orderform b ON a.cus_order_id = b.id')
            ->join('LEFT JOIN crm_customer c ON b.cus_id = c.cid')
            ->join('LEFT JOIN crm_staff d ON d.id = b.picid')
            ->join('LEFT JOIN crm_dept e ON e.id = d.deptid')
            ->join('LEFT JOIN crm_order_invoice f ON f.type_id    = b.invoice_type')
            ->join('LEFT JOIN crm_settlementlist g ON g.settle_id = b.settlement_method')
            ->join('LEFT JOIN crm_order_collection_type h ON h.id = a.settle_type')
            ->join('LEFT JOIN crm_order_performance_type q ON q.type_id = b.static_type')
            ->where($map)
            ->limit($sqlCondition['start'], $sqlCondition['length'])
            ->order($sqlCondition['order'])
            ->select();
        return [$count, $recordsFiltered, $rst];
    }

}