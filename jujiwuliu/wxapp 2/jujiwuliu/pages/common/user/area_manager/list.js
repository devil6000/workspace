var app = getApp();
Page({
  data: {
    siteinfo: app.siteInfo,
    provinces: [],
    provincesIndex: 0,
    citys: [],
    citysIndex: 0,
    districts: [],
    districtsIndex: 0,
    list: {},
    agreement: true,
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

  /**
 * 获取城市
 * id
 * type 层级
 * address_component 参考地址
 */
  get_citys: function (id, type, address_component) {
    var that = this;
    app.util.request({
      url: 'entry/wxapp/getchildren',
      data: { id: id },
      cache: false,
      success: function (res) {
        var areas = [];
        var objectareas = [];
        var areasIndex = 0;
        res.data.data.result[0].forEach(function (pro, idx) {
          areas.push(pro.fullname);
          objectareas[idx] = pro;
          objectareas[idx].name = pro.fullname;
          objectareas[idx].fullname = pro.name;
          if (address_component != 0) {
            if (type == 2) {
              if (pro.fullname == address_component.city) {
                areasIndex = idx;
              }
            }
            if (type == 3) {
              if (pro.fullname == address_component.district) {
                areasIndex = idx;
              }
            }
          }
        })

        if (type == 2) {
          that.setData({
            citys: areas,
            objectCitys: res.data.data.result[0],
            citysIndex: areasIndex
          })
          that.get_citys(areasIndex ? res.data.data.result[0][areasIndex].id : res.data.data.result[0][0].id, 3, address_component ? address_component : 0);
        }
        if (type == 3) {
          that.setData({
            districts: areas,
            objectDistricts: res.data.data.result[0],
            districtsIndex: areasIndex
          })
        }
      }
    })
  },

  on_loadlist: function (formid) {
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

    var area = that.data.objectProvinces[that.data.provincesIndex].name + ' ' + that.data.objectCitys[that.data.citysIndex].name + ' ' + that.data.objectDistricts[that.data.districtsIndex].name;

    var page = that.data.page ? that.data.page + 1 : 1;
    app.util.request({
      url: 'entry/wxapp/getAreaManagerList',
      cachetime: 0,
      data: { page: page, area: area, formid: formid },
      success: function (res) {
        var info = (that.data.list && page > 1) ? that.data.list : [];

        var list = res.data.data.list;
        if (list.length > 0) {
          for (var i = 0; i < list.length; i++) {
            info.push(list[i])
          }
        }


        that.setData({
          page: page,
          list: info,
          aloading: 0
        })
      }
    })
  },

  provinceChange: function (e) {
    var that = this;
    that.setData({
      provincesIndex: e.detail.value
    })
    that.get_citys(that.data.objectProvinces[e.detail.value].id, 2, 0);
  },

  cityChange: function (e) {
    var that = this;
    that.setData({
      citysIndex: e.detail.value
    })
    that.get_citys(that.data.objectCitys[e.detail.value].id, 3, 0);
  },

  districtChange: function (e) {
    var that = this;
    that.setData({
      districtsIndex: e.detail.value
    })
  },

  search: function(e){
    var that = this;
    var formid = e.detail.formId;
    that.setData({
      page: 0
    })
    that.on_loadlist(formid);
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

  bindchange: function (e) {//轮播图无限滚动
    this.setData({ current: e.detail.current })
  },

  callPhone: function(e){
    wx.makePhoneCall({
      phoneNumber: e.currentTarget.dataset.mobile
    })
  },

  onLoad: function (options) {
    var that = this;
    var setting = wx.getStorageSync('setting_set');
    that.setData({
      setting: setting.data
    });

    var userType = wx.getStorageSync('userType');
    if (userType == 'issuer') {
      that.setData({
        userType: 'issuer'
      })
    } else if (userType == 'worker') {
      that.setData({
        userType: 'worker'
      })
    }
    app.util.footer(that);

    wx.getLocation({
      type: 'wgs84',
      success: function (res) {
        const latitude = res.latitude;
        const longitude = res.longitude;
        const speed = res.speed;
        const accuracy = res.accuracy;

        app.util.request({
          url: 'entry/wxapp/getlocation',
          data: { lat: latitude, lng: longitude },
          success: function (res) {
            var data = res.data.data.result;
            app.util.request({
              url: 'entry/wxapp/getchildren',
              cache: false,
              success: function (res) {
                var provinces = [];
                var objectProvinces = [];
                var provincesIndex = 0;
                res.data.data.result[0].forEach(function (pro, idx) {
                  provinces.push(pro.fullname);
                  objectProvinces[idx] = pro;
                  objectProvinces[idx].name = pro.fullname;
                  objectProvinces[idx].fullname = pro.name;
                  if (data) {
                    if (pro.fullname == data.address_component.province) {
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
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

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
    this.on_loadlist('');
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})
