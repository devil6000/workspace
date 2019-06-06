// jujiwuliu/pages/common/user/area_manager/index.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    siteinfo: app.siteInfo,
    tabBar: [],
    sexArray: ['保密','男','女'],
    sexIndex: 0,
    staticArray: ['个人','企业'],
    staticIndex: 0,
    provinces: [],
    provincesIndex: 0,
    citys: [],
    citysIndex: 0,
    districts: [],
    districtsIndex: 0,
    areas: [],
    areasIndex: 0,
    areaManager: {},
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
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    var setting = wx.getStorageSync('setting_set');
    that.setData({
      setting: setting.data
    });

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

  bindchange: function (e) {//轮播图无限滚动
    this.setData({ current: e.detail.current })
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
        that.setData({
          areaManager: data
        })
      }
    });

    /*
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
    */
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
            if(type == 4){
              if(pro.fullname == address_component.street){
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
          that.get_citys(areasIndex ? res.data.data.result[0][areasIndex].id : res.data.data.result[0][0].id, 4, address_component ? address_component : 0);
        }
        if(type == 4){
          that.setData({
            areas: areas,
            objectAreas: res.data.data.result[0],
            areasIndex: areasIndex
          })
        }
      }
    })
  },

  provinceChange: function(e){
    var that = this;
    that.setData({
      provincesIndex: e.detail.value
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
    that.get_citys(that.data.objectDistricts[e.detail.value].id, 4, 0);
  },

  areaChange: function(e){
    var that = this;
    that.setData({
      areasIndex: e.detail.value
    })
  },
  staticPickerChange: function(e){
    this.setData({
      staticIndex: e.detail.value
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
  getWeixinhao: function(e){
    this.setData({
      weixinhao: e.detail.value
    })
  },
  changeSelect: function(e){
    var checked = e.target.dataset.checked;
    this.setData({
      agreement: checked == true ? false : true
    })
  },
  submit: function(e){
    var that = this;

    //判断手机号是否正确
    var exp = /^((1[1-9]{1})+\d{9})$/;
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
        district: that.data.objectDistricts[that.data.districtsIndex].name,
        area: that.data.objectAreas[that.data.areasIndex].name
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

    if(that.data.staticIndex == 0 && that.data.id_card == ''){
      wx.showModal({
        title: '温馨提示',
        content: '身份证号不能为空',
        showCancel: false
      })
      return
    }

    if(that.data.staticIndex == 1 && (that.data.images == undefined || that.data.images.length == 0 )){
      wx.showModal({
        title: '温馨提示',
        content: '营业执照不能为空',
        showCancel: false
      })
      return
    }

    if(!that.data.agreement){
      wx.showModal({
        title: '温馨提示',
        content: '请选择同意协议',
      })
    }

    app.util.request({
      url: 'entry/wxapp/setAreaManager',
      data: {
        formid: e.detail.formId,
        realname: that.data.realname,
        mobile: that.data.mobile,
        idcard: that.data.id_card,
        image: that.data.images,
        static: that.data.staticIndex,
        address: that.data.objectProvinces[that.data.provincesIndex].name + ' ' + that.data.objectCitys[that.data.citysIndex].name + ' ' + that.data.objectDistricts[that.data.districtsIndex].name + ' ' + that.data.objectAreas[that.data.areasIndex].name
      },
      method: "POST",
      success: function(res){
        if(res.data.errno == 0){
          that.onShow();
        }
      }
    })
  },

  bindChooiceProduct: function(){
    var that = this;
    wx.chooseImage({
      count: 1,
      sizeType: ['compressed'],
      sourceType: ['album','camera'],
      success: function(res) {
        var tempFilePaths = res.tempFilePaths;
        wx.showToast({
          title: '正在上传...',
          icon: 'loading',
          mask: true,
          duration: 1000
        })

        that.setData({
          images: []
        })

        tempFilePaths.forEach(function(file_path,index){
          wx.uploadFile({
            url: app.util.getUrl('entry/wxapp/imgupload'),
            filePath: file_path,
            name: 'file',
            formData: {
              file: file_path
            },
            header: {
              "Content-Type": "multipart/form-data"
            },
            success: function(res){
              var info = JSON.parse(res.data);
              if(info.errno == 0){
                info = info.data;
                var inf_imgs = that.data.images;
                inf_imgs.push(info.file_name);
                that.setData({
                  images: inf_imgs
                })
              }else{
                wx.showModal({
                  title: '获取失败',
                  content: info.message,
                  showCancel: false
                })
                return false;
              }
            }
          })
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