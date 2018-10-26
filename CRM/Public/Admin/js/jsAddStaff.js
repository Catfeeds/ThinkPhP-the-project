var addDepartInit = function(_cmbProvince, _cmbCity, _cmbArea, defaultProvince, defaultCity, defaultArea)
{
    // 三级联动方法，定义省市区
    var cmbProvince = document.getElementById(_cmbProvince);
    var cmbCity = document.getElementById(_cmbCity);
    var cmbArea = document.getElementById(_cmbArea);

    function cmbSelect(cmb, str)
    {
        for(var i=0; i<cmb.options.length; i++)
        {
            if(cmb.options[i].value == str)
            {
                cmb.selectedIndex = i;
                return;
            }
        }
    }

    // js创建option
    function cmbAddOption(cmb, str, obj)
    {
        var option = document.createElement("OPTION");
        cmb.options.add(option);
        option.innerText = str;
        option.value = str;
        option.obj = obj;
    }

    function changeCity()
    {
        cmbArea.options.length = 0;
        if(cmbCity.selectedIndex == -1)return;
        var item = cmbCity.options[cmbCity.selectedIndex].obj;
        for(var i=0; i<item.areaList.length; i++)
        {
            cmbAddOption(cmbArea, item.areaList[i], null);
        }
        cmbSelect(cmbArea, defaultArea);
    }
    function changeProvince()
    {
        cmbCity.options.length = 0;
        cmbCity.onchange = null;
        if(cmbProvince.selectedIndex == -1)return;
        var item = cmbProvince.options[cmbProvince.selectedIndex].obj;
        for(var i=0; i<item.cityList.length; i++)
        {
            cmbAddOption(cmbCity, item.cityList[i].name, item.cityList[i]);
        }
        cmbSelect(cmbCity, defaultCity);
        changeCity();
        cmbCity.onchange = changeCity;
    }

    // 读取遍历省份
    for(var i=0; i<provinceList.length; i++)
    {
        cmbAddOption(cmbProvince, provinceList[i].name, provinceList[i]);
    }
    cmbSelect(cmbProvince, defaultProvince);
    changeProvince();
    cmbProvince.onchange = changeProvince;
}

var provinceList = [
    {name:'研发1部', cityList:[
        {name:'', areaList:['部门经理','研发工程师']},
    ]},
    {name:'研发本部', cityList:[
        {name:'', areaList:['研发经理','研发后备人才','研发工程师']},
    ]},
    {name:'研发2部', cityList:[
        {name:'', areaList:['部门经理','研发工程师','实习生']},
    ]},
    {name:'研发3部', cityList:[
        {name:'应用研发组', areaList:['研发组长','研发工程师','助理工程师','网管']},
        {name:'硬件研发组', areaList:['研发组长','研发工程师','售后专员','维修员','烧录员','装配员','包装员']}
    ]},
    {name:'研发4部', cityList:[
        {name:'', areaList:['部门经理','研发工程师']}
    ]},
    {name:'研发5部', cityList:[
        {name:'', areaList:['部门经理','研发工程师','实习生','网管']}
    ]},
    {name:'销售1部', cityList:[
        {name:'销售本部', areaList:['部门经理','销售精英','销售后备人才']},
        {name:'销售1组', areaList:['销售组长','销售精英','销售后备人才']},
        {name:'销售2组', areaList:['销售组长','销售精英','销售后备人才']},
        {name:'销售3组', areaList:['销售组长','销售精英','销售后备人才']},
        {name:'销售4组', areaList:['销售组长','销售精英','销售后备人才']},
        {name:'销售5组', areaList:['销售组长','销售精英','销售后备人才']},
        {name:'销售6组', areaList:['销售组长','销售精英','销售后备人才']},
    ]},
    {name:'销售2部', cityList:[
        {name:'销售本部', areaList:['部门经理','销售精英','销售工程师','行政助理']},
        {name:'销售1组', areaList:['销售组长','销售工程师','销售后备人才']},
        {name:'销售2组', areaList:['销售组长','销售工程师','销售后备人才']},
        {name:'销售3组', areaList:['销售组长','销售工程师','销售后备人才']},
        {name:'销售5组', areaList:['销售组长','销售工程师','销售后备人才']}
    ]},
    {name:'销售3部', cityList:[
        {name:'销售本部', areaList:['部门经理','销售精英']}
    ]},
    {name:'销售6部', cityList:[
        {name:'销售本部', areaList:['部门经理','销售精英','销售工程师']},
        {name:'销售1组', areaList:['销售组长','销售后备人才']},
        {name:'销售2组', areaList:['销售组长','销售后备人才']},
        {name:'销售3组', areaList:['销售组长','销售后备人才','实习生']},
        {name:'销售4组', areaList:['销售组长','销售后备人才']},
        {name:'销售5组', areaList:['销售组长','销售后备人才']}
    ]}
];
