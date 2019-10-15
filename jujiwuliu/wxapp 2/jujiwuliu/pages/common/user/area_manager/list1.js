// jujiwuliu/pages/common/user/area_manager/list.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    siteinfo: app.siteInfo,
    multiArea: [],
    multiAreaIndex: [],
    objectMultiArea: [],
    provinces: [],
    provincesIndex: 0,
    citys: [],
    citysIndex: 0,
    areas: [],
    areasIndex: 0,
    districts: [],
    districtsIndex: 0,
    streetsIndex: 0,
    is_complete: false,  //获取地址完成
    //是否采用衔接滑动
    circular: true,
    //是否显示画板指示点
    indicatorDots: true,
    //选中点的颜色
    indicatorcolor: "#000",//被css冲突了
    //是否竖直
    vertical: false,
    //是否自动切换
    autoplay: true,
    //滑动动画时长毫秒
    duration: 1000,
    //所有图片的高度
    imgheights: [500],//不填写则默认获取图片高度（比较耗资源）
    //图片宽度
    imgwidth: 750,
    //默认
    current: 0,
  },

  on_loadlist: function (){
    var that = this;
    if (that.data.aloading == 1) {
      return false;
    }
    that.setData({
      aloading: 1
    })
    setTimeout(function () {//无论有没有加载完成 2秒后都自动退出加载状态
      that.setData({
        aloading: 0
      })
    }, 2000)

    var area = [];
    that.data.multiAreaIndex.forEach(function(pro,idx){
      area.push(that.data.multiArea[idx][pro]);
    })
    area = area.join(' ');

    var page = that.data.page ? that.data.page + 1 : 1;
    app.util.request({
      url: 'entry/wxapp/getAreaManagerList',
      cachetime: 0,
      data: { page: page, area: area},
      success: function (res) {
        var curArea = {};
        var info = (that.data.list && page > 1) ? that.data.list : [];
        curArea = res.data.data.curArea;

        var list = res.data.data.list;
        if(list.length > 0){
          for (var i = 0; i < list.length; i++) {
            info.push(list[i])
          }
        }


        that.setData({
          page: page,
          list: info,
          curArea: curArea,
          aloading: 0
        })
      }
    })
  },

  /**
 * 获取城市
 * id
 * type 层级
 * address_component 参考地址
 */
  get_citys: function (id, type, address_component) {
    var that = this;

    var multiAreas = that.data.multiArea;
    var multiAreasIndex = that.data.multiAreaIndex;
    var objectMultiAreas = that.data.objectMultiArea;
    var areasIndex = 0;
    var index = 0;        //新数组中的位置
    var chooseIndex = 0;  //总数组中的位置

    if(id == 0){
      for(var i = type; i < multiAreas.length; i++){
        multiAreas[i] = [];
        multiAreasIndex[i] = 0;
      }

      that.setData({
        multiArea: multiAreas,
        multiAreaIndex: multiAreasIndex
      })

      return false;
    }

    if(type == 1){  //市
      var tempAreas = [];
      var tmpId = that.cut_string(id,0,2);
      objectMultiAreas[type].forEach(function(pro,idx){
        var tmpSubId = that.cut_string(pro.id,0,2);
        if(tmpId == tmpSubId){
          tempAreas.push(pro.fullname);
          if(address_component){
            if(pro.fullname == address_component.city){
              chooseIndex = idx;
              areasIndex = index;
              multiAreasIndex[type] = areasIndex;
            }
            ++index;
          }else{
            var tmpSubCityId = that.cut_string(pro.id,2,4);
            if (parseInt(tmpSubCityId) == 1){
              chooseIndex = idx;
              areasIndex = index;
              multiAreasIndex[type] = areasIndex;
            }
          }
        }
      })
      multiAreas[type] = tempAreas;
      that.setData({
        multiArea: multiAreas,
        multiAreaIndex: multiAreasIndex
      })
      if(tempAreas.length > 0){  //判断是否有后面都地区
        that.get_citys(chooseIndex ? objectMultiAreas[type][chooseIndex].id : objectMultiAreas[type][0].id, 2, address_component ? address_component : 0);
      }else{
        that.get_citys(0,2,0);
      }
    }
    if(type == 2){  //区
      var tempAreas = [];
      var tmpProvinceId = that.cut_string(id,0,2);  //对应省
      var tmpCityId = that.cut_string(id,2,4);      //对应市/区
      objectMultiAreas[type].forEach(function(pro,idx){
        var tmpSubProvinceId = that.cut_string(pro.id,0,2);
        var tmpSubCityId = that.cut_string(pro.id,2,4);
        if((tmpProvinceId == tmpSubProvinceId) && (tmpCityId == tmpSubCityId)){
          tempAreas.push(pro.fullname);
          if(address_component){
            if(pro.fullname == address_component.district){
              chooseIndex = idx;
              areasIndex = index;
              multiAreasIndex[type] = areasIndex;
            }
          }else{
            if(index == 0){
              chooseIndex = idx;
              areasIndex = index;
              multiAreasIndex[type] = areasIndex;
            }
          }
          ++index;
        }
      })
      multiAreas[type] = tempAreas;
      that.setData({
        multiArea: multiAreas,
        multiAreaIndex: multiAreasIndex
      })
      if(tempAreas.length > 0){
        that.get_citys(chooseIndex ? objectMultiAreas[type][chooseIndex].id : objectMultiAreas[type][0].id, 3, address_component ? address_component : 0);        
      }else{
        that.get_citys(0,3,0)
      }
    }
    if(type == 3){  //街道
      app.util.request({
        url: 'entry/wxapp/getchildren',
        data: {id: id},
        cache: false,
        success: function (prores){
          var tempAreas = [];
          multiAreasIndex[type] = 0;
          prores.data.data.result[0].forEach(function(pro,idx){
            tempAreas.push(pro.fullname);
            if(address_component){
              if(pro.fullname == address_component.street){
                multiAreasIndex[type] = idx;
              }
            }
          })

          multiAreas[type] = tempAreas;
          that.setData({
            multiArea: multiAreas,
            multiAreaIndex: multiAreasIndex,
            is_complete: true
          })
        }
      })
    }
  },

  cut_string: function(str,start,len){
    return str.substring(start,len);
  },


  mulitColumnChange: function(e){
    var that = this;
    var objectMultiAreas = that.data.objectMultiArea;
    var multiAreasIndex = that.data.multiAreaIndex;
    var multiAreas = that.data.multiArea;
    var index = e.detail.column;
    var value = e.detail.value;
    multiAreasIndex[index] = value;
    that.setData({
      multiAreaIndex: multiAreasIndex
    })

    if(!objectMultiAreas[index]){ //没有地区的，后边都为空
      return false;
    }

    var type = index + 1;
    objectMultiAreas[index].forEach(function(pro,idx){
      if(pro.fullname == multiAreas[index][value]){
        console.log(pro.id)
        that.get_citys(pro.id,type,0);
      }
    })
  },

  submit: function(e){
    this.setData({
      page: 0
    })
    this.on_loadlist();
  },

  callPhone: function(e){
    wx.makePhoneCall({
      phoneNumber: e.currentTarget.dataset.mobile,
    })
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    var setting = wx.getStorageSync('setting_set');
    that.setData({
      setting: setting.data
    })

    var userType = wx.getStorageSync('userType');
    if(userType == 'issuer'){
      that.setData({
        userType: 'issuer'
      })
    }else if(userType == 'worker'){
      that.setData({
        userType: 'worker'
      })
    }
    app.util.footer(that);
    
    wx.getLocation({
      type: 'wgs84',
      success: function(res) {
        const latitude = res.latitude;
        const longitude = res.longitude;
        const speed = res.speed;
        const accuracy = res.accuracy;

        app.util.request({
          url: 'entry/wxapp/getlocation',
          data: { lat: latitude, lng: longitude },
          success: function(res){
            var data = res.data.data.result;
            var multiAreas = [];
            var multiAreasIndex = [];
            var objectMultiAreas = [];
            app.util.request({
              url: 'entry/wxapp/getAllArea',
              cache: false,
              success: function(res){
                var provinces = [];
                var objectProvinces = [];
                var provincesIndex = 0;
                res.data.data.result.forEach(function (pro, idx) {
                  var tempAreas = [];
                  var tempObjectAreas = [];
                  pro.forEach(function(item,key){
                    tempAreas.push(item.fullname);
                    tempObjectAreas[key] = item;
                    if(data){
                      if(item.fullname == data.address_component.province){
                        provincesIndex = key;
                        multiAreasIndex[idx] = provincesIndex;
                      }else if(item.fullname == data.address_component.city){
                        multiAreasIndex[idx] = key;
                      }else if(item.fullname == data.address_component.district){
                        multiAreasIndex[idx] = key;
                      }else if(item.fullname == data.address_component.street){
                        multiAreasIndex[idx] = key;
                      }
                    }
                  })
                  multiAreas[idx] = tempAreas;
                  objectMultiAreas[idx] = tempObjectAreas;
                })
                that.setData({
                  multiArea: multiAreas,
                  multiAreaIndex: multiAreasIndex,
                  objectMultiArea: objectMultiAreas
                })
                that.get_citys(provincesIndex ? objectMultiAreas[0][provincesIndex].id : objectMultiAreas[0][0].id, 1, data.address_component ? data.address_component : 0);
              }
            })
          }
        })
      },
    })
  },

  imageLoad: function (e) {//轮播图高度自适应
    //获取图片真实宽度
    var imgwidth = e.detail.width,
      imgheight = e.detail.height,
      //宽高比
      ratio = imgwidth / imgheight;
    //计算的高度值

    var viewHeight = this.data.imgwidth / ratio;
    var imgheight = viewHeight
    var imgheights = this.data.imgheights
    //把每一张图片的高度记录到数组里
    imgheights.push(imgheight)
    this.setData({
      imgheights: imgheights,
    })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    var that = this;
    var loadding = setInterval(function(){
      if(that.data.is_complete){
        that.on_loadlist();
        clearInterval(loadding);
      }
    }, 1000)
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})