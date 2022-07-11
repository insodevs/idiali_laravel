<?php date_default_timezone_set('Europe/Kiev');
$now = date("Y-m-d H:i"); ?>
<?php echo '<?xml version="1.0"?>'; ?>
<yml_catalog date="{{$now}}">
    <shop>
        <name>idiali</name>
        <company>idiali</company>
        <url>https://idiali.prom.ua/</url>
        <currencies>
            <currency id="USD" rate="CB"/>
            <currency id="UAH" rate="1"/>
            <currency id="BYN" rate="CB"/>
            <currency id="KZT" rate="CB"/>
            <currency id="EUR" rate="CB"/>
        </currencies>
        <categories>
            @foreach ($categories as $category)
                <category id="{{$category["id"]}}" @if(isset($category["parent_id"])) parentId="{{$category["parent_id"]}}" @endif>{{$category["title"]}}</category>
            @endforeach
        </categories>
        <offers>
            @foreach ($products as $product)
                @if(isset($product["stock"]["value"], $product["product"]["group"], $product["image"]) && $product["prices"]["default_price"] > 0 && $product["stock"]["value"] > 0 && !empty($product["image"]))
                    <offer id="@if(isset($product["product"]["code"])){{$product["product"]["code"]}}@endif" selling_type="u" available="true" group_id="{{$product["product"]["group"]}}">
                        <name>{{$product["product"]["name"]}}</name>
                        <price>{{$product["prices"]["default_price"]}}</price>
                        @if($product["prices"]["opt_price"] > 0)
                        <prices>
                            <price>
                                <value>{{$product["prices"]["opt_price"]}}</value>
                                <quantity>10</quantity>
                            </price>
                        </prices>
                        @endif
                        <currencyId>UAH</currencyId>
                        @if(isset($product["category_id"]))
                            <categoryId>{{$product["category_id"]}}</categoryId>
                        @endif
                        @foreach ($product["image"] as $image)
                            <picture>https://idiali.com/public/img/{{$image["uuid"]}}.jpg</picture>
                        @endforeach
                        <pickup>true</pickup>
                        <delivery>true</delivery>
                        <vendor>Idiali</vendor>
                        <vendorCode>@if(isset($product["product"]["code"])){{$product["product"]["code"]}}@endif</vendorCode>
                        <country_of_origin>Україна</country_of_origin>
                        <description>{{$product["product"]["description"]}}</description>
                        <sales_notes>предоплата</sales_notes>
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
