<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
  <channel>
    <title>{{{ $path->getUrl() }}} - Madokami</title>
    <link>{{{URL::to('/')}}}{{{ $path->getUrl() }}}?t=rss</link>
    <description>{{{ $path->getUrl() }}} - Madokami</description>
    <pubDate>{{{ (new DateTime(date('Y-m-d H:i:s', $updated)))->format(DateTime::RFC822)}}}</pubDate>

    @foreach($children as $child)@if(!$child->isDir)<item>
      <title>{{{ $child->name }}}</title>
      <link>{{{URL::to('/')}}}{{{ $child->url }}}</link>
      <description><![CDATA[<a href="{{{ $child->url }}}">{{{ $child->name }}}</a>]]></description>
      <enclosure url="{{{URL::to('/')}}}{{{ $child->url }}}" type="{{{$child->mime}}}" length="{{{$child->rawSize}}}" />
      <guid>{{{URL::to('/')}}}{{{ $child->url }}}?length={{{ $child->rawSize }}}&amp;mtime={{{ $child->rawTime}}}</guid>
      <pubDate>{{{ (new DateTime(date('Y-m-d H:i:s', $child->rawTime)))->format(DateTime::RFC822)}}}</pubDate>
    </item>
    @endif @endforeach
</channel>
</rss>
