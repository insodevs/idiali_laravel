<?php date_default_timezone_set('Europe/Kiev');
$now = date("Y-m-d H:i"); ?>
<?php echo '<?xml version="1.0"?>'; ?>
<yml_catalog date="{{$now}}">
<shop>
<name>Интернет магазин idiali</name>
<company>ТМ idiali</company>
<url>https://idiali.com/</url>
<currencies>
<currency id="UAH" rate="1"/>
</currencies>
<categories>
@foreach ($categories as $category)
<category id="{{$category["id"]}}" @if(isset($category["parent_id"])) parentId="{{$category["parent_id"]}}" @endif>{{$category["title"]}}</category>
@endforeach
</categories>
<offers>
@foreach ($products as $product)
@if(isset($product["stock"]["value"], $product["product"]["group"], $product["image"]) && $product["stock"]["value"] > 0 && !empty($product["image"]))
<offer group_id="{{$product["product"]["group"]}}" id="@if(isset($product["product"]["code"])){{$product["product"]["code"]}}@endif" selling_type="u" available="true">
<name>{{$product["product"]["name"]}}</name>
<price>{{$product["prices"]["default_price"]}}</price>
<dropPrice>{{$product["prices"]["drop_price"]}}</dropPrice>
<rrPrice>{{$product["prices"]["default_price"]}}</rrPrice>
<prices>
<price>
<value>{{$product["prices"]["opt_price"]}}</value>
<quantity>10</quantity>
</price>
</prices>
<description>{{$product["product"]["description"]}}</description>
@if(isset($product["image"]))
@foreach ($product["image"] as $image)
<picture>https://idiali.com/public/img/{{$image["uuid"]}}.jpg</picture>
@endforeach
@endif
@if(isset($product["category_id"]))
<categoryId>{{$product["category_id"]}}</categoryId>
@endif
<currencyId>UAH</currencyId>
@if(isset($product["characteristics"]))
@foreach ($product["characteristics"] as $char => $value)
<param name="{{$char}}">{{$value}}</param>
@endforeach
@endif
</offer>
@endif
@endforeach
</offers>
</shop>
</yml_catalog>
