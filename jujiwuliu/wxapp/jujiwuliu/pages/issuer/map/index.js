// jujiwuliu/pages/issuer/map/index.js

var qqMapWx = require('../../../resource/js/qqmap-wx-jssdk.min.js')
var qqmapsdk

Page({

  /**
   * 页面的初始数据
   */
  data: {
    subkey: "MF2BZ-SUJCQ-ISK5A-GHYV5-LQU42-KPB3I",
    scale: 19,
    lat: '',
    lng: '',
    markers: [{
      iconPath: "../../../resource/images/location.png",
      id: 0,
      longitude: 0,
      latitude: 0,
      width: 30,
      height: 30,
    }],
    longitudeList:[],
    longitudeShow: false
  },

  regionchange(e) {

    var that = this
    if (e.type == 'end' && (e.causedBy == 'scale' || e.causedBy == 'drag')){
      var createMap = wx.createMapContext("map", this)
      createMap.getCenterLocation({
        type: 'gcj02',
        success: function(res){
          createMap.translateMarker({
            markerId: 0,
            destination: { longitude: res.longitude, latitude: res.latitude}
          })
          that.setData({
            latitude: res.latitude,
            longitude: res.longitude
          })
        }
      })
    }

    //console.log(e.type)
  },
  markertap(e) {
    console.log(e.markerId)
  },
  controltap(e) {
    console.log(e.controlId)
  },
  goback: function(){
    var pages = getCurrentPages() //获取当前页面参数
    var prevPages = pages[pages.length - 2] //上一页
    prevPages.setData({
      lat: this.data.latitude,
      lng: this.data.longitude
    })
    wx.navigateBack({})
  },
  getsuggest: function(e){
    var that = this
    var value = e.detail.value
    qqmapsdk.search({
      keyword: value,
      success: function(res){
        console.log(res)
        var data = res.data
        if(data.length > 0){
          that.setData({
            longitudeList: data,
            longitudeShow: true
          })
        }else{
          wx.showLoading({
            title: '搜索结果为空',
            duration: 1000
          })
          that.setData({
            longitudeShow: false
          })
        }
      },
      fail: function(res){
        console.log(res)
        if(res.status == 120){
          wx.showLoading({
            title: '搜索频率过快',
            duration: 1000
          })
        }
        that.setData({
          longitudeShow: false
        })
      }
    })
  },
  getlongitude: function(e){
    var lat = e.currentTarget.dataset.lat
    var lng = e.currentTarget.dataset.lng
    var markers = this.data.markers
    markers[0].latitude = lat
    markers[0].longitude = lng
    this.setData({
      lat: lat,
      lng: lng,
      latitude: lat,
      longitude: lng,
      markers: markers
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this
    var markers = that.data.markers
    markers[0].latitude = options.lat
    markers[0].longitude = options.lng
    that.setData({
      lat: options.lat,
      lng: options.lng,
      markers: markers
    })
    qqmapsdk = new qqMapWx({
      key: 'LKNBZ-PNP6U-5MPVP-2FNXM-U6BRE-LLB45'
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

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})