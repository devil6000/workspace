// jujiwuliu/pages/common/user/area_manager/index.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    tabBar: [],
    sexArray: ['保密','男','女'],
    sexIndex: 0,
    provinces: [],
    provincesIndex: 0,
    citys: [],
    citysIndex: 0,
    districts: [],
    districtsIndex: 0,
    areaManager: {}
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
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
    
    app.util.request({
      url: 'entry/wxapp/getAreaManaager',
      method: 'GET',
      success: function(res){
        var data = res.data.data;
        if(data){
          wx.navigateTo({
            url: '/jujiwuliu/pages/common/user/area_manager/apply',
          })
        }
      }
    });

    wx.getLocation({
      type: 'wgs84',
      success: function(res) {
        const latitude = res.latitude;
        const longitude = res.longitude;
        const speed = res.speed;
        const accuracy = res.accuracy;

        app.util.request({
          url: 'entry/wxapp/getlocation',
          data: {lat: latitude, lng: longitude},
          success: function(res){
            var data = res.data.data.result;
            app.util.request({
              url: 'entry/wxapp/getchildren',
              cache: false,
              success: function(res){
                var provinces = [];
                var objectProvinces = [];
                var provincesIndex = 0;
                res.data.data.result[0].forEach(function(pro,idx){
                  provinces.push(pro.fullname);
                  objectProvinces[idx] = pro;
                  objectProvinces[idx].name = pro.fullname;
                  objectProvinces[idx].fullname = pro.name;
                  if(data){
                    if(pro.fullname == data.address_component.province){
                      provincesIndex = idx;
                    }
                  }
                })
                that.get_citys(provincesIndex ? res.data.data.result[0][provincesIndex].id : res.data.data.result[0][0].id, 2, data.address_component ? data.address_component : 0);
                that.setData({
                  provinces: provinces,
                  objectProvinces: res.data.data.result[0],
                  provincesIndex: provincesIndex
                })
              }
            })
          }
        })
      },
    })
  },

  /**
   * 获取城市
   * id
   * type 层级
   * address_component 参考地址
   */
  get_citys: function (id, type, address_component){
    var that = this;
    app.util.request({
      url: 'entry/wxapp/getchildren',
      data: {id: id},
      cache: false,
      success: function(res){
        var areas = [];
        var objectareas = [];
        var areasIndex = 0;
        res.data.data.result[0].forEach(function(pro,idx){
          areas.push(pro.fullname);
          objectareas[idx] = pro;
          objectareas[idx].name = pro.fullname;
          objectareas[idx].fullname = pro.name;
          if(address_component != 0){
            if(type == 2){
              if(pro.fullname == address_component.city){
                areasIndex = idx;
              }
            }
            if(type == 3){
              if(pro.fullname == address_component.district){
                areasIndex = idx;
              }
            }
          }
        })

        if(type == 2){
          that.setData({
            citys: areas,
            objectCitys: res.data.data.result[0],
            citysIndex: areasIndex
          })
          that.get_citys(areasIndex ? res.data.data.result[0][areasIndex].id : res.data.data.result[0][0].id,3,address_component ? address_component :0);
        }
        if(type == 3){
          that.setData({
            districts: areas,
            objectDistricts: res.data.data.result[0],
            districtsIndex: areasIndex
          })
        }
      }
    })
  },

  provinceChange: function(e){
    var that = this;
    that.setData({
      provincesIndex: e.detial.value
    })
    that.get_citys(that.data.objectProvinces[e.detail.value].id,2,0);
  },

  cityChange: function(e){
    var that = this;
    that.setData({
      citysIndex: e.detail.value
    })
    that.get_citys(that.data.objectCitys[e.detail.value].id,3,0);
  },

  districtChange: function(e){
    var that = this;
    that.setData({
      districtsIndex: e.detail.value
    })
  },
  sexPickerChange: function(e){
    var that = this;
    that.setData({
      sexIndex: e.detail.value
    })
  },
  getRealname: function(e){
    this.setData({
      realname: e.detail.value
    })
  },
  getMobile: function(e){
    this.setData({
      mobile: e.detail.value
    })
  },
  getAgo: function(e){
    this.setData({
      ago: e.detail.value
    })
  },
  getIdCard: function(e){
    this.setData({
      id_card: e.detail.value
    })
  },
  submit: function(e){
    var that = this;

    //判断手机号是否正确
    var exp = /^((1[1-9]{2})+\d{8})$/;
    if(!exp.test(that.data.mobile)){
      wx.showModal({
        title: '温馨提示',
        content: '手机号格式不正确',
        showCancel: false,
      })
      
      return;
    }

    //判断地区是否已经被注册
    app.util.request({
      url: 'entry/wxapp/getAreaManagerArea',
      data: {
        province: that.data.objectProvinces[that.data.provincesIndex].name,
        city: that.data.objectCitys[that.data.citysIndex].name,
        district: that.data.objectDistricts[that.data.districtsIndex].name
      },
      method: "POST",
      success: function(res){
        if(res.data.errno == 1){
          wx.showModal({
            title: '温馨提示',
            content: res.data.message,
            showCancel: false
          })
          return
        }
      }
    })

    if(that.data.realname == ''){
      wx.showModal({
        title: '温馨提示',
        content: '姓名不能为空',
        showCancel: false
      })
      return
    }

    if(that.data.id_card == ''){
      wx.showModal({
        title: '温馨提示',
        content: '身份证号不能为空',
        showCancel: false
      })
      return
    }

    app.util.request({
      url: 'entry/wxapp/setAreaManager',
      data: {
        formid: e.detail.formId,
        realname: that.data.realname,
        mobile: that.data.mobile,
        ago: that.data.ago,
        sex: that.data.sexIndex,
        idcard: that.data.id_card,
        address: that.data.objectProvinces[that.data.provincesIndex].name + ' ' + that.data.objectCitys[that.data.citysIndex].name + ' ' + that.data.objectDistricts[that.data.districtsIndex].name
      },
      method: "POST",
      success: function(res){
        if(res.status == 0){
          wx.navigateTo({
            url: '/jujiwuliu/pages/common/user/area_manager/apply',
          })
        }
      }
    })
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