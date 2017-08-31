	
	var h = 400;
  	var w = 900;
  	var menu = d3.select('#context-menu');
  	var menuState = 0;
  	var active = 'context-menu--active';
  	var non_active = 'context-menu';
  	//var element;
  	var dataObject = null;
  	var dataIndex = null;
  	var dataLen = 0;
  	var barLen = 0;
  	var removedElements = [];
  	var allData = []
  	var barData = [];
  	


  	var mousePosition = {x : -1, y: -1};
		
  	var init = function(){
		
		d3.select('#loading')
			.style('width', w+100)
			.style('height', h+100);

		d3.select('#chart_div')
			.append('svg')
			.attr('class', 'bBox')
	  		.attr('width',w + 100)
	  		.attr('height', h + 100)
	  		.attr('id', 'bBox')
	  		.style('display', 'block');
	  		
	  		



			  		
			
	}

	var updateLabel = function(str){

		
			d3.select('#hashtag_text')
			.text(str);
		

		
	}
  	
	var elDelete = function(){
		d3.select('#lBox').remove();
	}
	
	var positionMenu = function(){
		menuPosition = mousePosition;
		//console.log(menuPosition);
		menuPosX = menuPosition.x + "px";
		menuPosY = menuPosition.y + "px";
		//console.log(menuPosX, menuPosY);
		menu.style('left', menuPosX);
		menu.style('top' , menuPosY);
	}


	
	var clk = function(str){
		alert(str);
	}

	var addToList = function(data,index){

		d3.select('#element-list').append('li')
			.attr('class', 'element')
			.append('a')
			.attr('id','element' + index)
			.attr('href','#')
			.attr('onclick', 'addToChart(this);')
			.attr("index",index)
			.text('#'+ data.association.substring(0,14));
	}

	var removeFromList = function(index){
		d3.select('#element'+index).remove();
	}

	$('#remove_link').click(function(){
		console.log("[dat,ind]:", [dataObject,dataIndex]);
		removeFromChart(dataObject, dataIndex);

	})

	var removeFromChart = function(data,index){
		
		addToList(data, index);
		//console.log("dataLen: ", barData.length);
		//removedElements.push([data, index]);
		//barData.splice(index, 1)
		barData[index].hidden = 1;
		console.log("removedElements: ", barData[index].hidden);
		
		bar_chart2(barData);

	}

	var addToChart = function(link){
		console.log("addToChart");
		index = link.getAttribute('index');
		barData[index].hidden = 0;
		removeFromList(index);
		bar_chart2(barData);
	}

	
	
	
	var toggleMenuOn = function(){

		if(menuState != 1){
			menuState = 1;
			menu.classed(active, true);
			menu.classed(non_active, false);
		}

	}
	var toggleMenuOff = function(){


		if (menuState == 1) {
			menuState = 0;
			menu.classed(active , false);
			menu.classed(non_active, true);
		};

	}


	var bar_chart = function(hashtags){
		
	  	//console.log("hashtags", hashtags);
	  	//d3.select('#bBox').selectAll('*').remove();
	  	d3.select('#bBox').remove();
	  	d3.select('#loading').attr('class', 'hide');
	  	var tip = d3.tip()
			.attr('class', 'd3-tip')
		  	.offset([-10, 0])
	  		.html(function(d){
				return "<span style:'color:red'>#" + d.association + "</span>";
			});
	  	
	  	var data = hashtags.map(function(d){
	  		//console.log('d', d);
	  		association = d.association;
	  		count = +d.assoc_count;
	  		return{
	  			"association" : association,
	  			"count" : count,
	  			"hidden": 0
	  		};
	  	})
	  	
	  	barData = data;
	  	//filteredBarData = barData.filter(function(d){ return (d.hidden == 0)});
	  	
	  	console.log("dataLen : ", dataLen);
	  		
	  	var max = d3.max(barData, function(d){
	  		return d.count;
	  	});
	  	//console.log('max:', max);
	  	var scaleX = d3.scale.ordinal()
	  		.domain(d3.range(barData.length))
	  		.rangeBands([0,w], .25);
	  		
	  	var scaleY = d3.scale.linear()
	  		.domain([0,max])
	  		.range([h,0]);
	  		//console.log("max count: ", max);

	  	var svg = d3.select('#chart_div')
	  		.append('svg')
	  		.attr('class', 'bBox')
	  		.attr('width',w + 100)
	  		.attr('height', h + 100)
	  		.attr('id', 'bBox')
	  		.attr('oncontextmenu', "return false")
	  		.on('click', function(d){
	  			toggleMenuOff();
	  			updateLabel("");
	  		})
	  		.on('mousemove', function(d){
				var coordinates = [0,0]
				coordinates = d3.mouse(this);
				mousePosition.x = coordinates[0];
				mousePosition.y = coordinates[1];
	
	  		})
	  		.on("mouseout",function(){
	  			//updateLabel("");
	  		});
	  	
	  	$('#context-menu').click(function(){
	  		toggleMenuOff();
	  	})
	  	var text = svg
	  			.append('text')
	  			//.attr('transform', 'transform('+ w/2 +',0)')
	  			.attr('y', 10)
	  			.attr('x', w/2)
	  			.attr('dy', '1.5em')
	  			//.style('text-align', 'center')
	  			.text('Frequency');

	  	var xAxis = d3.svg.axis()
	  		.scale(scaleX)
	  		.orient('bottom');

	  	var yAxis = d3.svg.axis()
	  		.scale(scaleY)
	  		.orient('left');

	  	svg.call(tip);

	  		/*svg.append('g')
	  			.attr('class', 'xAxis')
	  			.attr('transform', 'translate(0,' + h + ")")
	  			.call(xAxis);*/

	  		svg.append('g')
	  			.attr('class', 'y axis')
	  			.call(yAxis)
	  			.attr('transform', 'translate(50,50)');

	  	barLen = scaleX.rangeBand();

	  	var bargroup = svg
	  		.append('g')
	  		.attr('transform', 'translate(50,50)');
	  		
	  	var bar = bargroup.selectAll('rect')
	  		.data(barData)
	  		.enter()
	  		.append('rect')
	  		.attr('class', 'bar')
	  		.attr('height', function(d){
	  			//console.log("count: ", d.count)
	  			return h-scaleY(d.count);
	  		})
	  		.attr('id', function(d,i){
	  			return "bar"+i;
	  		})
	  		.attr('width', scaleX.rangeBand())
	  		.attr('fill', 'blue')
	  		.attr('border', '1px solid red')
	  		.attr('transform', function(d, i){
	  			return "translate(" + [scaleX(i), scaleY(d.count)] +")";
	  		})
	  		.on('mouseover', function(d,i){
	  			
	  			dataObject = d;
	  			dataIndex = i;
	  			updateLabel("#"+d.association);
	  		})
	  		.on('mouseout', function(){
	  			
	  		})
	  		.on('click', function(d){
	  			line_graph(d.association);
	  		})
	  		.on('contextmenu', function(e,i){
	  			
	  			console.log(e);
	  			toggleMenuOn();
	  			//element =  e;
	  			
	  			positionMenu();
	  		});
	  		

		}


	var bar_chart2 = function(hashtags){
		
	  	//console.log("hashtags", hashtags);
	  	d3.select('#bBox').remove();
	  	
	  	var tip = d3.tip()
			.attr('class', 'd3-tip')
		  	.offset([-10, 0])
	  		.html(function(d){
				return "<span style:'color:red'>#" + d.association + "</span>";
			});
	  	
	  	console.log("dataLen : ", dataLen);
	  		
	  	var max = d3.max(barData.filter(function(d){ return (d.hidden == 0)}), function(d){
	  		return d.count;
	  	});
	  	//console.log('max:', max);
	  	var scaleX = d3.scale.ordinal()
	  		.domain(d3.range(barData.filter(function(d){ return (d.hidden == 0)}).length))
	  		.rangeBands([0,w], .25);
	  		
	  	var scaleY = d3.scale.linear()
	  		.domain([0,max])
	  		.range([h,0]);
	  		//console.log("max count: ", max);

	  	var svg = d3.select('#chart_div')
	  		.append('svg')
	  		.attr('class', 'bBox')
	  		.attr('width',w + 100)
	  		.attr('height', h + 100)
	  		.attr('id', 'bBox')
	  		.attr('oncontextmenu', "return false")
	  		.on('click', function(d){
	  			toggleMenuOff();
	  			updateLabel("");
	  			
	  		})
	  		.on('mousemove', function(d){
				var coordinates = [0,0]
				coordinates = d3.mouse(this);
				mousePosition.x = coordinates[0];
				mousePosition.y = coordinates[1];
	
	  		})
	  		.on("mouseout",function(){
	  			//updateLabel("");
	  		});
	  	
	  	
	  	var text = svg
	  			.append('text')
	  			//.attr('transform', 'transform('+ w/2 +',0)')
	  			.attr('y', 10)
	  			.attr('x', w/2)
	  			.attr('dy', '1.5em')
	  			//.style('text-align', 'center')
	  			.text('Frequency');

	  	var xAxis = d3.svg.axis()
	  		.scale(scaleX)
	  		.orient('bottom');

	  	var yAxis = d3.svg.axis()
	  		.scale(scaleY)
	  		.orient('left');

	  	svg.call(tip);

	  		/*svg.append('g')
	  			.attr('class', 'xAxis')
	  			.attr('transform', 'translate(0,' + h + ")")
	  			.call(xAxis);*/

	  		svg.append('g')
	  			.attr('class', 'y axis')
	  			.call(yAxis)
	  			.attr('transform', 'translate(50,50)');

	  	barLen = scaleX.rangeBand();

	  	var bargroup = svg
	  		.append('g')
	  		.attr('transform', 'translate(50,50)');
	  		
	  	var bar = bargroup.selectAll('rect')
	  		.data(barData)
	  		
	  		.enter()
	  		.append('rect')
	  		.filter(function(d){ return (d.hidden == 0)})
	  		.attr('class', 'bar')
	  		.attr('height', function(d){
	  			//console.log("count: ", d.count)
	  			return h-scaleY(d.count);
	  		})
	  		.attr('id', function(d,i){
	  			return "bar"+i;
	  		})
	  		.attr('width', scaleX.rangeBand())
	  		.attr('fill', 'blue')
	  		.attr('border', '1px solid red')
	  		.attr('transform', function(d, i){
	  			return "translate(" + [scaleX(i), scaleY(d.count)] +")";
	  		})
	  		.on('mouseover', function(d,i){
	  			updateLabel("#"+d.association);
	  			dataObject = d;
	  			dataIndex = i;
	  		})
	  		.on('mouseout', function(){
	  			
	  		})
	  		.on('click', function(d){
	  			line_graph(d.association);
	  		})
	  		.on('contextmenu', function(e,i){
	  			
	  			console.log(e);
	  			toggleMenuOn();
	  			//element =  e;
	  			
	  			positionMenu();
	  		});
	  		

		}
	var formatDate = function(date){
		var year = date.getFullYear();
		var day = date.getDate();
		var month = date.getMonth();
		return year + "-" + month + "-" + day;
	}

	$("input[name = 'maxDate']").val(formatDate(Date.now()));
	$("input[name = 'minDate']").val(formatDate(Date.now()));


	var line_graph = function(hashtag){

		var maxDate = $("input[name = 'maxDate']").val();
		var minDate = $("input[name = 'minDate']").val();

		console.log(maxDate,minDate);
		var svg = d3.select('#chart_div').append('svg')
	  				.attr('class', 'bBox')
			  		.attr('width',w + 100)
			  		.attr('height', h + 100)
			  		.attr('id', 'lBox')
			  		.style('display', 'block')
			  		.on('click', function(d){
			  			elDelete();
			  		});

			  	svg.selectAll('*').remove();
		
		$.ajax({
			type: "POST",
			dataType : "json",
			data: { "hashtag" : hashtag, "maxDate" : maxDate, "minDate" : minDate},

			error: function(that, e){
				console.log(e);
				console.log(that);
			},
			url: "hashtag_freq.php",
			success: function(response){
				console.log("response", response);
				if(response.length ==  0){

					svg.append('text')
						.attr('class', 'noResponse')
						.text('No Response')
						.attr('x', 100)
						.attr('y', 100);
						

				}else{

				var timeFormat = d3.time.format("%Y-%m-%d");

				var data = response.map(function(d){
			  		//console.log('d', d);
			  		datetime = timeFormat.parse(d.datetime);
			  		count = +d.assoc_count;
			  		return{
			  			"datetime" : datetime
			  			,"count" : count
			  		};
			  	})

				var scaleX = d3.time.scale()
					.range([0,w])
					.domain(d3.extent(data, function(d){return d.datetime}));



				var max = d3.max(data, function(d){
			  		return d.count;
	  			});
				
				var formatXAxis = d3.format('.0f');

				var scaleY = d3.scale.linear()
			  		.domain([0,max])
			  		.range([h,0]);
			  		
			  	var xAxis = d3.svg.axis()
			  		.scale(scaleX)
			  		.orient('bottom');

			  	var yAxis = d3.svg.axis()
			  		.scale(scaleY)
			  		.orient('left')
			  		.tickFormat(formatXAxis)
			  		.ticks(max);

				

			  	var lineGroup = svg.append('g')
			  		.attr('transform', 'translate(50,50)');

				var text = svg
	  			.append('text')
	  			//.attr('transform', 'transform('+ w/2 +',0)')
	  			.attr('y', 10)
	  			.attr('x', w/2)
	  			.attr('dy', '1.5em')
	  			//.style('text-align', 'center')
	  			.text(hashtag);

	  			var line = d3.svg.line()
	  				.x(function(d){
	  					return scaleX(d.datetime);
	  				})
	  				.y(function(d){
	  					return scaleY(d.count);
	  				});

	  			var dotGroup = svg.append('g')
	  				.attr('transform', 'translate(50,50)');
	  			//	.call(xAxis);
	  			svg.append('g')
	  				.call(yAxis)
	  				.attr('class', 'y axis')
	  				.attr('transform', 'translate(50,50)');

	  			svg.append('g')
	  				.call(xAxis)
	  				.attr('class','x axis')
	  				.attr('transform', 'translate(50,'+ (h+ 50) +')');

	  			var path = lineGroup.append("path")
	  					//.datum(data)
	  					.attr("d", line(data))
	  					.attr('class', 'line')
	  			var dot = dotGroup.selectAll("dot")
	  				.data(data)
	  				.enter()
	  				.append("circle")
	  				.attr('r', 3)
	  				.attr("cx",function(d){ return scaleX( d.datetime)})
	  				.attr("cy",function(d){ return scaleY( d.count)});
	  			}	
				
			  	
			  	//console.log("data: ", data);
			  		
			  	
	  	
			}
		});

		

	  		
		}

