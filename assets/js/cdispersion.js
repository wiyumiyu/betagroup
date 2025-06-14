const w = 900,
      h = 500,
      padding = 85;

(async function() {
  try {
    let res = await fetch('https://gist.githubusercontent.com/javiervaleriano/bfa72215bb6c782ab01af852a23314d0/raw/'),
        json = await res.json();
    
    if (!res.ok) throw { status: res.status, statusText: res.statusText };
    
    const svg = d3.select('#chart-container')
      .append('svg')
      .attr('width', w + 80)
      .attr('height', h + 30);
    
    const tooltip = d3.select('body').append('div')
                       .attr('id', 'tooltip')
                       .style('position', 'absolute');
    
    let years = json.map(function(data, i) {
      return i === 0 ? new Date(1993, 00) :
             i === 1 ? new Date(2016, 00) : new Date(data.Year, 00);
    });
    
    const xScale = d3.scaleTime()
    .domain([d3.min(years), d3.max(years)])
    .range([0, w - 80]);
    svg.append('g')
       .attr('transform', `translate(${padding}, ${h})`)
       .attr('id', 'x-axis')
       .call(d3.axisBottom(xScale));
    
    let times = json.map(time => time.Seconds);
    
    const yScale = d3.scaleLinear()
                     .domain([d3.max(times), d3.min(times)])
                     .range([h - 10, 0]);
    svg.append('g')
       .attr('transform', `translate(${padding}, 10)`)
       .attr('id', 'y-axis')
       .call(d3.axisLeft(yScale));
    
    svg.append('text')
       .style('font-variant', 'small-caps')
       .attr('transform', 'rotate(-90)')
       .attr('x', -200)
       .attr('y', 30)
       .style('font-size', 28)
       .text('Tiempo en minutos');
    
    document.querySelectorAll('#y-axis g.tick text').forEach(function(txt, i) {
      let val = txt.textContent.split('').filter(item => item !== ',').join('');
      val = parseInt(val);
      let mins = Math.floor(val / 60),
          secs = val % 60;
      
      txt.textContent = secs < 10 ? `${mins}:0${secs}` : `${mins}:${secs}`;
    });
    
    svg.selectAll('circle')
       .data(json)
       .enter()
       .append('circle')
       .attr('index', (d, i) => i)
       .classed('dot', true)
       .style('fill', ({Doping}) => Doping ? '#FF2626' : '#09009B')
       .attr('data-xvalue', d => d.Year)
       .attr('data-yvalue', d => new Date(d.Year, 00, 1, 00, 00, d.Seconds))
       .attr('r', 6.75)
       .attr('cx', ({Year}) => padding + xScale(new Date(Year, 0)))
       .attr('cy', ({Seconds}) => yScale(Seconds) + 10)
       .on('mouseover', function(e) {
         let i = this.getAttribute('index'),
             data = json[i],
             year = this.getAttribute('data-xvalue');
      
         tooltip.attr('data-year', year)
                .html(data.Doping ? `<p>${data.Name}: ${data.Nationality}</p><p>Año: ${year}; Tiempo: ${data.Time}</p><p class='mt-doping'>${data.Doping}</p>` : `<p class='mb-sindoping'>${data.Name}: ${data.Nationality}</p><p>Año: ${year}; Tiempo: ${data.Time}</p>`)
                .style('left', `${e.pageX + 15}px`)
                .style('top', `${e.pageY - 50}px`)
                .transition().duration(0).style('opacity', 1).style('z-index', 100);
       })
      .on('mouseout', function() {
         tooltip.transition().duration(250).style('opacity', 0).style('z-index', -100);
      });
    
    const legendInfo = [
    {
      color: '#FF2626',
      doping: true
    },
    {
      color: '#09009B',
      doping: false
    }
    ];
    
    let legendCont = svg.append('g').attr('id', 'legend');
    
    let legend = legendCont.selectAll('#legend')
          .data(legendInfo)
    .enter()
          .append('g')
          .attr('class', 'legend-label')
          .attr('transform', (d, i) => `translate(85, ${90 + (i * 20)})`);
    
    legend.append('rect')
          .attr('x', 840)
          .attr('y', 98)
          .attr('width', 14)
          .attr('height', 14)
          .style('fill', ({doping, color}) => doping ? color : color);
    
    legend.append('text')
          .attr('x', w - 65)
          .attr('y', 110)
          .attr('text-anchor', 'end')
          .text(({doping}) => doping ? 'Corredores con acusaciones de dopaje' : 'Sin acusaciones de dopaje');
    
  } catch(err) {
    console.log(err);
  }
})();