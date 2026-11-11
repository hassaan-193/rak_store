<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard | FTS</title>
  <meta name="robots" content="noindex, nofollow">


  <link rel="icon" type="image/png" href="https://ftsits.com/wp-content/uploads/2025/02/ftsits-logo-fav.png">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&amp;display=fallback">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
  
  <!-- iCheck -->
  <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  
  <!-- JQVMap -->
  <link rel="stylesheet" href="{{ asset('plugins/jqvmap/jqvmap.min.css') }}">
  
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min2167.css?v=3.2.0') }}">
  
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
  
  <!-- Daterange picker -->
  <link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
  
  <!-- summernote -->
  <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
  
  <!-- Custom style -->
  <link rel="stylesheet" href="{{ asset('dist/css/style.css') }}">

  <script data-cfasync="false" nonce="32490ad4-1f36-4554-9934-7c7d18c39451">
    try {
      (function(w, d) {
        !function(bj, bk, bl, bm) {
          if (bj.zaraz) console.error("zaraz is loaded twice");
          else {
            bj[bl] = bj[bl] || {};
            bj[bl].executed = [];
            bj.zaraz = { deferred: [], listeners: [] };
            bj.zaraz._v = "5852";
            bj.zaraz._n = "32490ad4-1f36-4554-9934-7c7d18c39451";
            bj.zaraz.q = [];
            bj.zaraz._f = function(bn) {
              return async function() {
                var bo = Array.prototype.slice.call(arguments);
                bj.zaraz.q.push({ m: bn, a: bo });
              };
            };
            for (const bp of ["track", "set", "debug"]) bj.zaraz[bp] = bj.zaraz._f(bp);
            bj.zaraz.init = () => {
              var bq = bk.getElementsByTagName(bm)[0],
                  br = bk.createElement(bm),
                  bs = bk.getElementsByTagName("title")[0];
              bs && (bj[bl].t = bk.getElementsByTagName("title")[0].text);
              bj[bl].x = Math.random();
              bj[bl].w = bj.screen.width;
              bj[bl].h = bj.screen.height;
              bj[bl].j = bj.innerHeight;
              bj[bl].e = bj.innerWidth;
              bj[bl].l = bj.location.href;
              bj[bl].r = bk.referrer;
              bj[bl].k = bj.screen.colorDepth;
              bj[bl].n = bk.characterSet;
              bj[bl].o = (new Date).getTimezoneOffset();
              if (bj.dataLayer) for (const bt of Object.entries(Object.entries(dataLayer).reduce(((bu, bv) => ({ ...bu[1], ...bv[1] })), {}))) zaraz.set(bt[0], bt[1], { scope: "page" });
              bj[bl].q = [];
              for (; bj.zaraz.q.length;) {
                const bw = bj.zaraz.q.shift();
                bj[bl].q.push(bw);
              }
              br.defer = !0;
              for (const bx of [localStorage, sessionStorage]) Object.keys(bx || {}).filter((bz => bz.startsWith("_zaraz_"))).forEach((by => {
                try {
                  bj[bl]["z_" + by.slice(7)] = JSON.parse(bx.getItem(by));
                } catch {
                  bj[bl]["z_" + by.slice(7)] = bx.getItem(by);
                }
              }));
              br.referrerPolicy = "origin";
              br.src = "../../cdn-cgi/zaraz/sd0d9.js?z=" + btoa(encodeURIComponent(JSON.stringify(bj[bl])));
              bq.parentNode.insertBefore(br, bq);
            };
            ["complete", "interactive"].includes(bk.readyState) ? zaraz.init() : bj.addEventListener("DOMContentLoaded", zaraz.init);
          }
        }(w, d, "zarazData", "script");
        window.zaraz._p = async bb => new Promise((bc => {
          if (bb) {
            bb.e && bb.e.forEach((bd => {
              try {
                const be = d.querySelector("script[nonce]"),
                      bf = be?.nonce || be?.getAttribute("nonce"),
                      bg = d.createElement("script");
                bf && (bg.nonce = bf);
                bg.innerHTML = bd;
                bg.onload = () => { d.head.removeChild(bg); };
                d.head.appendChild(bg);
              } catch (bh) {
                console.error(`Error executing script: ${bd}\n`, bh);
              }
            }));
            Promise.allSettled((bb.f || []).map((bi => fetch(bi[0], bi[1]))));
          }
          bc();
        }));
        zaraz._p({ "e": ["(function(w,d){})(window,document)"] });
      })(window, document);
    } catch (e) {
      throw fetch("/cdn-cgi/zaraz/t"), e;
    };
  </script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
