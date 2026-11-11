@extends('layouts.app')

@section('title', 'Dashboard | FTS')

@section('content')
<style>
  .content{
    margin-left:5%;
  }
  .small-box{
    margin-top:15%;
  }
</style>
<section class="content">
  <div class="container-fluid">
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info text-bg-primary-inner1">
              <div class="inner">
                <h3>{{ $incomingMaterialCount }}</h3>
                <p>Incoming Material</p>
              </div>
              <div class="icon">
                <i class="ion ion-bag"></i>
              </div>
              <a href="{{ route('incoming-materials.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning text-bg-primary-inner2">
              <div class="inner">
                <h3>{{ $outgoingMaterialPercentage }}<sup style="font-size: 20px"></sup></h3>
                <p>Outgoing Material</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="{{ route('outgoing-materials.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning text-bg-primary-inner2">
              <div class="inner">
                <h3>{{ $returnableMaterialCount }}</h3>
                <p>Returnable Materials</p>
              </div>
              <div class="icon">
                <i class="ion ion-android-refresh"></i>
              </div>
              <a href="{{ route('return-materials.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning text-bg-primary-inner2">
              <div class="inner">
                <h3>{{ $returnableToolCount ?? 0 }}</h3>
                <p>Tools Movements</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
              <a href="{{ route('tool-returns.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
  </div>
</section>
@endsection
